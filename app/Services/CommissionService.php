<?php

namespace App\Services;

use App\Data\Integrations\B2cPaymentData;
use App\Data\Integrations\SmsMessage;
use App\Enums\ActivityEvent;
use App\Enums\JobCardStatus;
use App\Models\Commission;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\JobCard;
use App\Models\User;
use App\Support\CommissionSettings;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CommissionService
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_PAID = 'paid';

    public const PAYMENT_MANUAL = 'manual';

    public const PAYMENT_MPESA = 'mpesa';

    protected const MPESA_PAYOUT_CACHE_PREFIX = 'commission_mpesa_payout:';

    protected const MPESA_OTP_TTL_SECONDS = 600;

    protected const MPESA_OTP_MAX_ATTEMPTS = 5;

    public function __construct(
        protected IntegrationService $integrationService,
        protected ActivityLogService $activityLogService,
    ) {}

    public function recordForJobCard(JobCard $jobCard, string $event): ?Commission
    {
        if (! $this->shouldAccrue($event) || ! $jobCard->assigned_to) {
            return null;
        }

        $jobCard->loadMissing(['services', 'invoice']);

        $baseAmount = $this->baseAmountForJobCard($jobCard, $event);

        if ($baseAmount <= 0) {
            return null;
        }

        return $this->createCommission(
            employeeId: (int) $jobCard->assigned_to,
            branchId: (int) $jobCard->branch_id,
            reference: $jobCard,
            baseAmount: $baseAmount,
            event: $event,
            earnedOn: $this->earnedOnForJobCard($jobCard, $event),
        );
    }

    public function recordForInvoice(Invoice $invoice, string $event): ?Commission
    {
        if (! $this->shouldAccrue($event)) {
            return null;
        }

        $invoice->loadMissing('jobCard');

        $jobCard = $invoice->jobCard;

        if (! $jobCard?->assigned_to) {
            return null;
        }

        $baseAmount = (float) $invoice->total_amount;

        if ($baseAmount <= 0) {
            return null;
        }

        return $this->createCommission(
            employeeId: (int) $jobCard->assigned_to,
            branchId: (int) $invoice->branch_id,
            reference: $jobCard,
            baseAmount: $baseAmount,
            event: $event,
            earnedOn: $invoice->issued_at?->toDateString() ?? now()->toDateString(),
        );
    }

    /** @return Collection<int, array{employee: Employee, washes: int, earned: float, pending: float, paid: float, commission_ids: list<int>}> */
    public function dailySummary(?int $branchId, ?Carbon $date = null): Collection
    {
        $date = ($date ?? now())->toDateString();

        $washCounts = JobCard::query()
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->where('status', JobCardStatus::Completed)
            ->whereDate('completed_at', $date)
            ->whereNotNull('assigned_to')
            ->selectRaw('assigned_to, COUNT(*) as washes')
            ->groupBy('assigned_to')
            ->pluck('washes', 'assigned_to');

        $commissions = Commission::query()
            ->with('employee')
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->whereDate('earned_on', $date)
            ->get();

        $employeeIds = $washCounts->keys()
            ->merge($commissions->pluck('employee_id'))
            ->unique()
            ->filter();

        $employees = Employee::query()
            ->whereIn('id', $employeeIds)
            ->orderBy('full_name')
            ->get()
            ->keyBy('id');

        return $employeeIds->map(function (int $employeeId) use ($employees, $washCounts, $commissions) {
            $employee = $employees->get($employeeId);

            if (! $employee || $employee->isSupervisor()) {
                return null;
            }

            $rows = $commissions->where('employee_id', $employeeId);

            return [
                'employee' => $employee,
                'washes' => (int) ($washCounts[$employeeId] ?? 0),
                'earned' => (float) $rows->sum('amount'),
                'pending' => (float) $rows->where('status', self::STATUS_PENDING)->sum('amount'),
                'paid' => (float) $rows->where('status', self::STATUS_PAID)->sum('amount'),
                'commission_ids' => $rows->pluck('id')->values()->all(),
            ];
        })->filter()->values();
    }

    /** @return array{started: bool, payout_token?: string, amount?: float, employee_name?: string, otp_sent_to?: string, message: string} */
    public function initiateMpesaPayout(Employee $employee, Carbon $date, User $initiator): array
    {
        $dateString = $date->toDateString();

        if (blank($employee->phone)) {
            return [
                'started' => false,
                'message' => 'Employee phone number is required to send M-Pesa.',
            ];
        }

        $adminPhone = $this->adminPhoneForOtp($initiator);

        if (blank($adminPhone)) {
            return [
                'started' => false,
                'message' => 'Add a phone number to your user profile or company settings to receive payout OTPs.',
            ];
        }

        $pending = Commission::query()
            ->where('employee_id', $employee->id)
            ->where('branch_id', $employee->branch_id)
            ->whereDate('earned_on', $dateString)
            ->where('status', self::STATUS_PENDING)
            ->get();

        if ($pending->isEmpty()) {
            return [
                'started' => false,
                'message' => 'No pending commissions for this day.',
            ];
        }

        $amount = (float) $pending->sum('amount');
        $otp = $this->generatePayoutOtp();
        $payoutToken = (string) Str::uuid();

        Cache::put(self::MPESA_PAYOUT_CACHE_PREFIX.$payoutToken, [
            'employee_id' => $employee->id,
            'branch_id' => $employee->branch_id,
            'date' => $dateString,
            'commission_ids' => $pending->pluck('id')->values()->all(),
            'amount' => $amount,
            'employee_phone' => $employee->phone,
            'employee_name' => $employee->full_name,
            'initiator_id' => $initiator->id,
            'otp_hash' => password_hash($otp, PASSWORD_BCRYPT),
            'attempts' => 0,
        ], self::MPESA_OTP_TTL_SECONDS);

        $this->integrationService->sms()->send(new SmsMessage(
            to: $adminPhone,
            message: sprintf(
                'AutoSpa: Your commission payout OTP is %s. Authorize KES %s to %s. Valid for 10 minutes.',
                $otp,
                number_format($amount, 0),
                $employee->full_name,
            ),
        ));

        $this->activityLogService->record(
            ActivityEvent::CommissionMpesaInitiated->value,
            "M-Pesa payout OTP sent for {$employee->full_name}",
            $employee,
            [
                'employee_id' => $employee->id,
                'date' => $dateString,
                'amount' => $amount,
                'commission_count' => $pending->count(),
                'otp_sent_to' => $this->maskPhone($adminPhone),
            ],
            $initiator->id,
            $employee->branch_id,
        );

        return [
            'started' => true,
            'payout_token' => $payoutToken,
            'amount' => $amount,
            'employee_name' => $employee->full_name,
            'otp_sent_to' => $this->maskPhone($adminPhone),
            'message' => 'Enter the OTP sent to '.$this->maskPhone($adminPhone).' to authorize this M-Pesa payout.',
            ...($this->shouldExposeDebugOtp() ? ['debug_otp' => $otp] : []),
        ];
    }

    /** @return array{paid: bool, amount: float, message: string, payment_reference?: string|null} */
    public function confirmMpesaPayout(string $payoutToken, string $otp, User $initiator): array
    {
        $cacheKey = self::MPESA_PAYOUT_CACHE_PREFIX.$payoutToken;
        $payload = Cache::get($cacheKey);

        if (! is_array($payload)) {
            return [
                'paid' => false,
                'amount' => 0.0,
                'message' => 'This payout authorization has expired. Please start again.',
            ];
        }

        if ((int) ($payload['initiator_id'] ?? 0) !== $initiator->id) {
            return [
                'paid' => false,
                'amount' => (float) ($payload['amount'] ?? 0),
                'message' => 'You are not authorized to confirm this payout.',
            ];
        }

        $payload['attempts'] = (int) ($payload['attempts'] ?? 0) + 1;

        if ($payload['attempts'] > self::MPESA_OTP_MAX_ATTEMPTS) {
            Cache::forget($cacheKey);

            return [
                'paid' => false,
                'amount' => (float) ($payload['amount'] ?? 0),
                'message' => 'Too many invalid OTP attempts. Please start the payout again.',
            ];
        }

        if (! password_verify($otp, (string) ($payload['otp_hash'] ?? ''))) {
            Cache::put($cacheKey, $payload, self::MPESA_OTP_TTL_SECONDS);

            return [
                'paid' => false,
                'amount' => (float) ($payload['amount'] ?? 0),
                'message' => 'Invalid OTP. Check the code sent to your phone and try again.',
            ];
        }

        return DB::transaction(function () use ($cacheKey, $payload, $initiator) {
            $pending = Commission::query()
                ->whereIn('id', $payload['commission_ids'] ?? [])
                ->where('status', self::STATUS_PENDING)
                ->lockForUpdate()
                ->get();

            if ($pending->isEmpty()) {
                Cache::forget($cacheKey);

                return [
                    'paid' => false,
                    'amount' => 0.0,
                    'message' => 'No pending commissions remain for this payout.',
                ];
            }

            $amount = (float) $pending->sum('amount');
            $date = (string) $payload['date'];
            $employeeId = (int) $payload['employee_id'];

            $result = $this->integrationService->mpesa()->initiateB2cPayment(
                new B2cPaymentData(
                    phone: (string) $payload['employee_phone'],
                    amount: $amount,
                    reference: sprintf('COM-%s-%s', $date, $employeeId),
                    description: 'Daily commission payout for '.($payload['employee_name'] ?? 'washer'),
                ),
            );

            if (! $result->successful) {
                return [
                    'paid' => false,
                    'amount' => $amount,
                    'message' => $result->message ?? 'M-Pesa payout failed.',
                ];
            }

            $b2cReference = $result->reference;

            $paidAt = now();

            Commission::query()
                ->whereIn('id', $pending->pluck('id'))
                ->update([
                    'status' => self::STATUS_PAID,
                    'paid_at' => $paidAt,
                    'payment_method' => self::PAYMENT_MPESA,
                    'payment_reference' => $b2cReference,
                ]);

            $this->logCommissionPayout(
                employeeId: $employeeId,
                branchId: (int) $payload['branch_id'],
                date: $date,
                amount: $amount,
                paymentMethod: self::PAYMENT_MPESA,
                paymentReference: $b2cReference,
                commissionIds: $pending->pluck('id')->values()->all(),
                userId: $initiator->id,
                employeeName: (string) ($payload['employee_name'] ?? 'washer'),
            );

            Cache::forget($cacheKey);

            return [
                'paid' => true,
                'amount' => $amount,
                'payment_reference' => $b2cReference,
                'message' => 'Commission sent via M-Pesa.',
            ];
        });
    }

    public function payEmployeeDaily(
        Employee $employee,
        ?Carbon $date = null,
        bool $sendMpesa = false,
    ): array {
        $date = ($date ?? now())->toDateString();

        return DB::transaction(function () use ($employee, $date, $sendMpesa) {
            $pending = Commission::query()
                ->where('employee_id', $employee->id)
                ->where('branch_id', $employee->branch_id)
                ->whereDate('earned_on', $date)
                ->where('status', self::STATUS_PENDING)
                ->lockForUpdate()
                ->get();

            if ($pending->isEmpty()) {
                return [
                    'paid' => false,
                    'amount' => 0.0,
                    'message' => 'No pending commissions for this day.',
                ];
            }

            $amount = (float) $pending->sum('amount');
            $paymentMethod = self::PAYMENT_MANUAL;
            $paymentReference = null;

            if ($sendMpesa) {
                return [
                    'paid' => false,
                    'amount' => $amount,
                    'message' => 'Use Send M-Pesa to authorize payout with OTP.',
                ];
            }

            $paidAt = now();

            Commission::query()
                ->whereIn('id', $pending->pluck('id'))
                ->update([
                    'status' => self::STATUS_PAID,
                    'paid_at' => $paidAt,
                    'payment_method' => $paymentMethod,
                    'payment_reference' => $paymentReference,
                ]);

            $this->logCommissionPayout(
                employeeId: $employee->id,
                branchId: $employee->branch_id,
                date: $date,
                amount: $amount,
                paymentMethod: $paymentMethod,
                paymentReference: $paymentReference,
                commissionIds: $pending->pluck('id')->values()->all(),
                userId: auth()->id(),
                employeeName: $employee->full_name,
            );

            return [
                'paid' => true,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'payment_reference' => $paymentReference,
                'message' => $sendMpesa
                    ? 'Commission sent via M-Pesa.'
                    : 'Commission marked as paid.',
            ];
        });
    }

    public function totalsForDate(?int $branchId, ?Carbon $date = null): array
    {
        $date = ($date ?? now())->toDateString();

        $query = Commission::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereDate('earned_on', $date);

        return [
            'earned' => (float) (clone $query)->sum('amount'),
            'pending' => (float) (clone $query)->where('status', self::STATUS_PENDING)->sum('amount'),
            'paid' => (float) (clone $query)->where('status', self::STATUS_PAID)->sum('amount'),
            'washers' => (int) JobCard::query()
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->where('status', JobCardStatus::Completed)
                ->whereDate('completed_at', $date)
                ->whereNotNull('assigned_to')
                ->distinct('assigned_to')
                ->count('assigned_to'),
        ];
    }

    public function todayTotals(?int $branchId): array
    {
        return $this->totalsForDate($branchId);
    }

    public function syncMissingCommissions(?int $branchId, ?Carbon $date = null): void
    {
        if (! CommissionSettings::enabled()) {
            return;
        }

        $dateString = ($date ?? now())->toDateString();
        $trigger = CommissionSettings::trigger();

        if (in_array($trigger, [CommissionSettings::TRIGGER_JOB_COMPLETED, CommissionSettings::TRIGGER_BOTH], true)) {
            JobCard::query()
                ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
                ->where('status', JobCardStatus::Completed)
                ->whereDate('completed_at', $dateString)
                ->whereNotNull('assigned_to')
                ->with(['services', 'invoice', 'assignee'])
                ->orderBy('id')
                ->each(fn (JobCard $jobCard) => $this->recordForJobCard(
                    $jobCard,
                    CommissionSettings::TRIGGER_JOB_COMPLETED,
                ));
        }

        if (in_array($trigger, [CommissionSettings::TRIGGER_POS_CHECKOUT, CommissionSettings::TRIGGER_BOTH], true)) {
            Invoice::query()
                ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
                ->whereDate('issued_at', $dateString)
                ->whereHas('jobCard', fn ($query) => $query->whereNotNull('assigned_to'))
                ->with('jobCard')
                ->orderBy('id')
                ->each(fn (Invoice $invoice) => $this->recordForInvoice(
                    $invoice,
                    CommissionSettings::TRIGGER_POS_CHECKOUT,
                ));
        }
    }

    protected function shouldAccrue(string $event): bool
    {
        if (! CommissionSettings::enabled()) {
            return false;
        }

        $trigger = CommissionSettings::trigger();

        return $trigger === CommissionSettings::TRIGGER_BOTH
            || $trigger === $event;
    }

    protected function createCommission(
        int $employeeId,
        int $branchId,
        JobCard $reference,
        float $baseAmount,
        string $event,
        string $earnedOn,
    ): ?Commission {
        $employee = Employee::query()->find($employeeId);

        if (! $employee || $employee->isSupervisor()) {
            return null;
        }

        if ($this->existsForReference($employeeId, $reference)) {
            return Commission::query()
                ->where('employee_id', $employeeId)
                ->where('reference_type', $reference->getMorphClass())
                ->where('reference_id', $reference->getKey())
                ->first();
        }

        $rate = CommissionSettings::defaultRate();

        return Commission::create([
            'employee_id' => $employeeId,
            'branch_id' => $branchId,
            'reference_type' => $reference->getMorphClass(),
            'reference_id' => $reference->getKey(),
            'amount' => round($baseAmount * $rate, 2),
            'rate' => $rate,
            'status' => self::STATUS_PENDING,
            'earned_on' => $earnedOn,
            'trigger_event' => $event,
        ]);
    }

    protected function existsForReference(int $employeeId, JobCard $reference): bool
    {
        return Commission::query()
            ->where('employee_id', $employeeId)
            ->where('reference_type', $reference->getMorphClass())
            ->where('reference_id', $reference->getKey())
            ->exists();
    }

    protected function baseAmountForJobCard(JobCard $jobCard, string $event): float
    {
        if ($event === CommissionSettings::TRIGGER_POS_CHECKOUT && $jobCard->invoice) {
            return (float) $jobCard->invoice->total_amount;
        }

        return (float) $jobCard->services->sum('price');
    }

    protected function earnedOnForJobCard(JobCard $jobCard, string $event): string
    {
        if ($event === CommissionSettings::TRIGGER_POS_CHECKOUT && $jobCard->invoice?->issued_at) {
            return $jobCard->invoice->issued_at->toDateString();
        }

        return $jobCard->completed_at?->toDateString() ?? now()->toDateString();
    }

    protected function adminPhoneForOtp(User $user): ?string
    {
        if (filled($user->phone)) {
            return $user->phone;
        }

        return Company::query()->value('phone');
    }

    protected function generatePayoutOtp(): string
    {
        if (app()->environment('testing')) {
            return '123456';
        }

        return (string) random_int(100000, 999999);
    }

    protected function maskPhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (strlen($digits) < 4) {
            return $phone;
        }

        $visible = substr($digits, -3);
        $prefix = substr($digits, 0, min(4, strlen($digits) - 3));

        return $prefix.str_repeat('•', max(3, strlen($digits) - strlen($prefix) - 3)).$visible;
    }

    protected function shouldExposeDebugOtp(): bool
    {
        return app()->environment(['local', 'testing']);
    }

    /**
     * @param  array<int, int>  $commissionIds
     */
    protected function logCommissionPayout(
        int $employeeId,
        int $branchId,
        string $date,
        float $amount,
        string $paymentMethod,
        ?string $paymentReference,
        array $commissionIds,
        ?int $userId,
        string $employeeName,
    ): void {
        $this->activityLogService->record(
            ActivityEvent::CommissionPaid->value,
            sprintf('Paid KES %s commission to %s for %s', number_format($amount, 2), $employeeName, $date),
            null,
            [
                'employee_id' => $employeeId,
                'date' => $date,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'payment_reference' => $paymentReference,
                'commission_ids' => $commissionIds,
            ],
            $userId,
            $branchId,
        );
    }
}
