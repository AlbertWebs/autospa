<?php

namespace App\Services\Sync;

use App\Enums\ActivityEvent;
use App\Enums\JobCardStatus;
use App\Enums\PaymentMethodType;
use App\Enums\VehicleStatus;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\JobCard;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Scopes\BranchScope;
use App\Models\Service;
use App\Models\SyncMutation;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\ActivityLogService;
use App\Services\PosService;
use App\Services\VehicleSmsNotificationService;
use App\Support\OfflineRoutes;
use App\Support\RegistrationNumber;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

class SyncService
{
    public function __construct(
        protected PosService $posService,
        protected VehicleSmsNotificationService $vehicleSmsNotificationService,
        protected ActivityLogService $activityLogService,
    ) {}

    public function bootstrap(int $branchId, ?User $user = null): array
    {
        $customers = Customer::query()
            ->where('branch_id', $branchId)
            ->with([
                'vehicles' => fn ($query) => $query
                    ->select(['id', 'uuid', 'customer_id', 'registration_number', 'make', 'model', 'color'])
                    ->orderByDesc('id'),
            ])
            ->orderBy('full_name')
            ->limit(500)
            ->get();

        return [
            'branch_id' => $branchId,
            'synced_at' => now()->toIso8601String(),
            'services' => Service::query()
                ->where('branch_id', $branchId)
                ->where('is_active', true)
                ->with('category')
                ->orderBy('name')
                ->get(),
            'products' => Product::query()
                ->where('branch_id', $branchId)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
            'payment_methods' => PaymentMethod::query()
                ->withoutGlobalScope(BranchScope::class)
                ->where('is_active', true)
                ->whereIn('slug', [
                    PaymentMethodType::Cash->value,
                    PaymentMethodType::Card->value,
                    PaymentMethodType::Bank->value,
                    PaymentMethodType::Mpesa->value,
                ])
                ->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)->orWhereNull('branch_id');
                })
                ->orderBy('name')
                ->get(),
            'employees' => Employee::query()
                ->assignableToJobCards($branchId)
                ->get(['id', 'uuid', 'full_name', 'position']),
            'customers' => $customers,
            'vehicles' => Vehicle::query()
                ->where('branch_id', $branchId)
                ->orderByDesc('id')
                ->limit(500)
                ->get(['id', 'uuid', 'customer_id', 'registration_number', 'make', 'model', 'color']),
            'pages' => OfflineRoutes::urlsForUser($user),
            'operable_routes' => OfflineRoutes::operableRouteNames(),
            'operable_menu' => OfflineRoutes::operableMenuForUser($user, false),
            'operable_menu_mobile' => OfflineRoutes::operableMenuForUser($user, true),
            'syncable_mutations' => OfflineRoutes::syncableMutations(),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $mutations
     * @return array<int, array<string, mixed>>
     */
    public function push(User $user, int $branchId, array $mutations): array
    {
        $results = [];
        $idMap = [];

        foreach ($mutations as $mutation) {
            $mutationId = (string) $mutation['id'];

            $existing = SyncMutation::query()
                ->where('client_mutation_id', $mutationId)
                ->first();

            if ($existing) {
                $results[] = array_merge(
                    ['id' => $mutationId, 'status' => 'duplicate'],
                    $existing->result ?? [],
                );

                $this->mergeIdMapFromResult($idMap, $existing->result ?? []);

                continue;
            }

            try {
                $result = $this->applyMutation(
                    $user,
                    $branchId,
                    (string) $mutation['type'],
                    $mutation['payload'] ?? [],
                    $mutation['client_entity_uuid'] ?? null,
                    $idMap,
                );

                SyncMutation::query()->create([
                    'client_mutation_id' => $mutationId,
                    'user_id' => $user->id,
                    'branch_id' => $branchId,
                    'type' => $mutation['type'],
                    'entity_uuid' => $mutation['client_entity_uuid'] ?? null,
                    'result' => $result,
                    'applied_at' => now(),
                ]);

                $this->activityLogService->record(
                    ActivityEvent::SyncMutationApplied->value,
                    'Offline sync: '.str_replace('.', ' ', (string) $mutation['type']),
                    null,
                    [
                        'mutation_id' => $mutationId,
                        'type' => $mutation['type'],
                        'result' => $result,
                    ],
                    $user->id,
                    $branchId,
                );

                $this->mergeIdMapFromResult($idMap, $result);

                $results[] = array_merge(['id' => $mutationId, 'status' => 'applied'], $result);
            } catch (Throwable $e) {
                $results[] = [
                    'id' => $mutationId,
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * @param  array<string, int|string>  $idMap
     * @return array<string, mixed>
     */
    protected function applyMutation(
        User $user,
        int $branchId,
        string $type,
        array $payload,
        ?string $clientEntityUuid,
        array &$idMap,
    ): array {
        $this->authorizeMutation($user, $type);

        return match ($type) {
            'customer.create' => $this->createCustomer($branchId, $payload, $clientEntityUuid, $idMap),
            'vehicle.create' => $this->createVehicle($branchId, $payload, $clientEntityUuid, $idMap),
            'job_card.create' => $this->createJobCard($branchId, $user->id, $payload, $clientEntityUuid, $idMap),
            'job_card.update_status' => $this->updateJobCardStatus($branchId, $payload, $idMap),
            'pos.checkout' => $this->checkoutPos($branchId, $user->id, $payload, $idMap),
            default => throw new InvalidArgumentException("Unsupported mutation type [{$type}]."),
        };
    }

    /**
     * @param  array<string, int|string>  $idMap
     * @return array<string, mixed>
     */
    protected function createCustomer(
        int $branchId,
        array $payload,
        ?string $clientEntityUuid,
        array &$idMap,
    ): array {
        $uuid = $clientEntityUuid ?? $payload['uuid'] ?? (string) Str::uuid();

        if (Customer::query()->where('uuid', $uuid)->exists()) {
            $customer = Customer::query()->where('uuid', $uuid)->firstOrFail();

            return $this->customerResult($customer, $idMap, $uuid);
        }

        [$customer, $vehicle] = DB::transaction(function () use ($branchId, $payload, $uuid) {
            $registrationNumber = filled($payload['registration_number'] ?? null)
                ? RegistrationNumber::normalize((string) $payload['registration_number'])
                : null;

            $customer = Customer::query()->create([
                'uuid' => $uuid,
                'branch_id' => $branchId,
                'full_name' => $payload['full_name'],
                'phone' => $payload['phone'] ?? null,
                'email' => $payload['email'] ?? null,
                'id_number' => $payload['id_number'] ?? null,
                'address' => $payload['address'] ?? null,
                'notes' => $payload['notes'] ?? null,
            ]);

            $vehicle = null;

            if ($registrationNumber) {
                $vehicleUuid = filled($payload['vehicle_uuid'] ?? null)
                    ? (string) $payload['vehicle_uuid']
                    : (string) Str::uuid();

                $vehicle = Vehicle::query()->where('uuid', $vehicleUuid)->first();

                if (! $vehicle) {
                    $vehicle = Vehicle::query()->create([
                        'uuid' => $vehicleUuid,
                        'branch_id' => $branchId,
                        'customer_id' => $customer->id,
                        'registration_number' => $registrationNumber,
                        'status' => VehicleStatus::Active,
                    ]);
                }
            }

            return [$customer, $vehicle];
        });

        if ($vehicle) {
            $this->vehicleSmsNotificationService->sendVehicleRegistered($customer, $vehicle);
        }

        $result = $this->customerResult($customer, $idMap, $uuid);

        if ($vehicle) {
            $result['vehicle'] = [
                'id' => $vehicle->id,
                'uuid' => $vehicle->uuid,
                'customer_id' => $vehicle->customer_id,
                'registration_number' => $vehicle->registration_number,
            ];
            $idMap["vehicle:{$vehicle->uuid}"] = $vehicle->id;
        }

        return $result;
    }

    /**
     * @param  array<string, int|string>  $idMap
     * @return array<string, mixed>
     */
    protected function createVehicle(
        int $branchId,
        array $payload,
        ?string $clientEntityUuid,
        array &$idMap,
    ): array {
        $uuid = $clientEntityUuid ?? $payload['uuid'] ?? (string) Str::uuid();
        $customerId = $this->resolveReference($payload['customer_id'] ?? null, $idMap);

        if ($customerId === null) {
            throw new InvalidArgumentException('Vehicle create requires a valid customer_id.');
        }

        if (Vehicle::query()->where('uuid', $uuid)->exists()) {
            $vehicle = Vehicle::query()->where('uuid', $uuid)->firstOrFail();

            return $this->vehicleResult($vehicle, $idMap, $uuid);
        }

        $vehicle = Vehicle::query()->create([
            'uuid' => $uuid,
            'branch_id' => $branchId,
            'customer_id' => $customerId,
            'registration_number' => RegistrationNumber::normalize($payload['registration_number'] ?? null),
            'make' => $payload['make'] ?? null,
            'model' => $payload['model'] ?? null,
            'year' => $payload['year'] ?? null,
            'color' => $payload['color'] ?? null,
            'vin' => $payload['vin'] ?? null,
            'mileage' => $payload['mileage'] ?? null,
            'status' => $payload['status'] ?? VehicleStatus::Active,
        ]);

        $vehicle->load('customer');

        if ($vehicle->customer) {
            $this->vehicleSmsNotificationService->sendVehicleRegistered($vehicle->customer, $vehicle);
        }

        return $this->vehicleResult($vehicle, $idMap, $uuid);
    }

    /**
     * @param  array<string, int|string>  $idMap
     * @return array<string, mixed>
     */
    protected function createJobCard(
        int $branchId,
        int $userId,
        array $payload,
        ?string $clientEntityUuid,
        array &$idMap,
    ): array {
        $uuid = $clientEntityUuid ?? $payload['uuid'] ?? (string) Str::uuid();
        $customerId = $this->resolveReference($payload['customer_id'] ?? null, $idMap);
        $vehicleId = $this->resolveReference($payload['vehicle_id'] ?? null, $idMap);

        if ($customerId === null) {
            throw new InvalidArgumentException('Job card create requires a valid customer_id.');
        }

        if (JobCard::query()->where('uuid', $uuid)->exists()) {
            $jobCard = JobCard::query()->where('uuid', $uuid)->firstOrFail();

            return $this->jobCardResult($jobCard, $idMap, $uuid);
        }

        $jobCard = JobCard::query()->create([
            'uuid' => $uuid,
            'branch_id' => $branchId,
            'customer_id' => $customerId,
            'vehicle_id' => $vehicleId,
            'booking_id' => $this->resolveReference($payload['booking_id'] ?? null, $idMap),
            'created_by' => $userId,
            'assigned_to' => $this->resolveReference($payload['assigned_to'] ?? null, $idMap),
            'status' => $payload['status'] ?? JobCardStatus::Open,
            'notes' => $payload['notes'] ?? null,
        ]);

        $serviceIds = $payload['service_ids'] ?? [];

        if ($serviceIds === []) {
            throw new InvalidArgumentException('Job card create requires at least one service.');
        }

        $this->attachJobCardServices($jobCard, $serviceIds);

        return $this->jobCardResult($jobCard, $idMap, $uuid);
    }

    /**
     * @param  array<int, int|string>  $serviceIds
     */
    protected function attachJobCardServices(JobCard $jobCard, array $serviceIds): void
    {
        foreach (array_values(array_unique(array_map('intval', $serviceIds))) as $serviceId) {
            $service = Service::query()
                ->where('branch_id', $jobCard->branch_id)
                ->where('id', $serviceId)
                ->where('is_active', true)
                ->first();

            if (! $service) {
                throw new InvalidArgumentException("Service [{$serviceId}] is not available for this branch.");
            }

            $jobCard->services()->create([
                'service_id' => $service->id,
                'price' => $service->price,
                'status' => 'pending',
            ]);
        }
    }

    /**
     * @param  array<string, int|string>  $idMap
     * @return array<string, mixed>
     */
    protected function updateJobCardStatus(int $branchId, array $payload, array &$idMap): array
    {
        $jobCard = $this->resolveJobCard($branchId, $payload['job_card_id'] ?? null, $idMap);

        $status = JobCardStatus::tryFrom((string) ($payload['status'] ?? ''));

        if (! $status instanceof JobCardStatus) {
            throw new InvalidArgumentException('Job card status update requires a valid status.');
        }

        $jobCard->update($this->statusAwarePayload($jobCard, [
            'status' => $status->value,
            'assigned_to' => $this->resolveReference($payload['assigned_to'] ?? $jobCard->assigned_to, $idMap),
            'notes' => $payload['notes'] ?? $jobCard->notes,
        ]));

        return $this->jobCardResult($jobCard->fresh(), $idMap, $jobCard->uuid);
    }

    /**
     * @param  array<string, int|string>  $idMap
     * @return array<string, mixed>
     */
    protected function checkoutPos(int $branchId, int $userId, array $payload, array &$idMap): array
    {
        if (($payload['method'] ?? null) === PaymentMethodType::Mpesa->value) {
            throw new InvalidArgumentException('M-Pesa checkout cannot be synced offline.');
        }

        $data = $payload;
        $data['customer_id'] = $this->resolveReference($payload['customer_id'] ?? null, $idMap);
        $data['vehicle_id'] = $this->resolveReference($payload['vehicle_id'] ?? null, $idMap);

        $jobCardRef = $payload['job_card_id'] ?? null;
        $data['job_card_id'] = ($jobCardRef === null || $jobCardRef === '')
            ? null
            : $this->resolveJobCard($branchId, $jobCardRef, $idMap)->id;

        if ($data['customer_id'] === null) {
            throw new InvalidArgumentException('POS checkout requires a valid customer_id.');
        }

        $receipt = $this->posService->checkout($branchId, $userId, $data);

        return [
            'server_id' => $receipt->id,
            'entity_type' => 'receipt',
            'receipt' => [
                'id' => $receipt->id,
                'receipt_number' => $receipt->receipt_number,
                'amount' => $receipt->amount,
            ],
            'redirect' => route('receipts.show', $receipt),
        ];
    }

    protected function resolveJobCard(int $branchId, mixed $reference, array $idMap): JobCard
    {
        if (is_string($reference) && str_starts_with($reference, 'client:')) {
            $uuid = substr($reference, 7);

            if (preg_match('/^server-job_card-(\d+)$/', $uuid, $matches) === 1) {
                return JobCard::query()->where('branch_id', $branchId)->findOrFail((int) $matches[1]);
            }

            $mappedId = $idMap["job_card:{$uuid}"] ?? null;

            if ($mappedId) {
                return JobCard::query()->where('branch_id', $branchId)->findOrFail($mappedId);
            }

            return JobCard::query()
                ->where('branch_id', $branchId)
                ->where('uuid', $uuid)
                ->firstOrFail();
        }

        $id = $this->resolveReference($reference, $idMap);

        return JobCard::query()->where('branch_id', $branchId)->findOrFail($id);
    }

    protected function resolveReference(mixed $value, array $idMap): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        if (is_string($value) && str_starts_with($value, 'client:')) {
            $uuid = substr($value, 7);

            // Electron bootstrap may invent local refs like client:server-customer-12
            // when the server row has no uuid yet — map those directly to the server id.
            if (preg_match('/^server-(customer|vehicle|job_card)-(\d+)$/', $uuid, $matches) === 1) {
                return (int) $matches[2];
            }

            foreach (['customer', 'vehicle', 'job_card'] as $entity) {
                if (isset($idMap["{$entity}:{$uuid}"])) {
                    return (int) $idMap["{$entity}:{$uuid}"];
                }
            }

            foreach ([Customer::class, Vehicle::class, JobCard::class] as $model) {
                $record = $model::query()->where('uuid', $uuid)->first();

                if ($record) {
                    return $record->id;
                }
            }

            throw new InvalidArgumentException("Unable to resolve client reference [{$value}].");
        }

        throw new InvalidArgumentException('Invalid reference value provided.');
    }

    protected function customerResult(Customer $customer, array &$idMap, string $uuid): array
    {
        $idMap["customer:{$uuid}"] = $customer->id;

        return [
            'server_id' => $customer->id,
            'entity_type' => 'customer',
            'customer' => [
                'id' => $customer->id,
                'uuid' => $customer->uuid,
                'full_name' => $customer->full_name,
                'phone' => $customer->phone,
            ],
        ];
    }

    protected function vehicleResult(Vehicle $vehicle, array &$idMap, string $uuid): array
    {
        $idMap["vehicle:{$uuid}"] = $vehicle->id;

        return [
            'server_id' => $vehicle->id,
            'entity_type' => 'vehicle',
            'vehicle' => [
                'id' => $vehicle->id,
                'uuid' => $vehicle->uuid,
                'customer_id' => $vehicle->customer_id,
                'registration_number' => $vehicle->registration_number,
            ],
        ];
    }

    protected function jobCardResult(JobCard $jobCard, array &$idMap, string $uuid): array
    {
        $idMap["job_card:{$uuid}"] = $jobCard->id;

        return [
            'server_id' => $jobCard->id,
            'entity_type' => 'job_card',
            'job_card' => [
                'id' => $jobCard->id,
                'uuid' => $jobCard->uuid,
                'status' => $jobCard->status?->value,
            ],
            'redirect' => route('job-cards.live'),
        ];
    }

    protected function statusAwarePayload(JobCard $jobCard, array $data): array
    {
        $status = $data['status'] ?? null;

        if (is_string($status)) {
            $status = JobCardStatus::tryFrom($status);
        }

        if (! $status instanceof JobCardStatus) {
            return $data;
        }

        return match ($status) {
            JobCardStatus::Open => array_merge($data, [
                'started_at' => null,
                'completed_at' => null,
            ]),
            JobCardStatus::InProgress => array_merge($data, [
                'started_at' => $jobCard->started_at ?? now(),
                'completed_at' => null,
            ]),
            JobCardStatus::Completed => array_merge($data, [
                'started_at' => $jobCard->started_at ?? now(),
                'completed_at' => now(),
            ]),
            JobCardStatus::Cancelled => array_merge($data, [
                'completed_at' => null,
            ]),
        };
    }

    protected function mergeIdMapFromResult(array &$idMap, array $result): void
    {
        if (isset($result['customer']['uuid'], $result['customer']['id'])) {
            $idMap["customer:{$result['customer']['uuid']}"] = $result['customer']['id'];
        }

        if (isset($result['vehicle']['uuid'], $result['vehicle']['id'])) {
            $idMap["vehicle:{$result['vehicle']['uuid']}"] = $result['vehicle']['id'];
        }

        if (isset($result['job_card']['uuid'], $result['job_card']['id'])) {
            $idMap["job_card:{$result['job_card']['uuid']}"] = $result['job_card']['id'];
        }
    }

    protected function authorizeMutation(User $user, string $type): void
    {
        $required = match ($type) {
            'customer.create' => ['customers.create'],
            'vehicle.create' => ['vehicles.manage'],
            'job_card.create', 'job_card.update_status' => ['job-cards.manage'],
            'pos.checkout' => ['pos.access'],
            default => [],
        };

        if ($required !== [] && ! $user->hasAnyPermission($required)) {
            throw new InvalidArgumentException('You do not have permission to sync this mutation.');
        }
    }
}
