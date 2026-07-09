<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AssignsBranchId;
use App\Http\Controllers\Concerns\SyncsJobCardServices;
use App\Http\Requests\StoreJobCardRequest;
use App\Http\Requests\UpdateJobCardRequest;
use App\Enums\JobCardStatus;
use App\Models\Booking;
use App\Models\Commission;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\JobCard;
use App\Models\Vehicle;
use App\Services\CommissionService;
use App\Services\PosService;
use App\Support\CommissionSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class JobCardController extends Controller
{
    use AssignsBranchId, SyncsJobCardServices;

    public function index(): View
    {
        $today = today();
        $query = fn () => JobCard::query()->forDay($today)->with(['customer', 'vehicle', 'assignee', 'creator'])->latest();
        $countQuery = fn () => JobCard::query()->forDay($today);

        return view('job-cards.index', [
            'today' => $today,
            'openJobCards' => $query()->where('status', JobCardStatus::Open)->get(),
            'inProgressJobCards' => $query()->where('status', JobCardStatus::InProgress)->get(),
            'completedJobCards' => $query()->where('status', JobCardStatus::Completed)->get(),
            'counts' => [
                'open' => $countQuery()->where('status', JobCardStatus::Open)->count(),
                'in_progress' => $countQuery()->where('status', JobCardStatus::InProgress)->count(),
                'completed' => $countQuery()->where('status', JobCardStatus::Completed)->count(),
            ],
        ]);
    }

    public function create(): View
    {
        $vehicles = Vehicle::query()->with('customer')->get();

        return view('job-cards.create', [
            'customers' => Customer::query()->orderBy('full_name')->get(),
            'vehicles' => $vehicles,
            'bookings' => Booking::query()
                ->linkableToJobCard()
                ->with('customer')
                ->latest('scheduled_at')
                ->limit(50)
                ->get(),
            'employees' => $this->assignableEmployees(),
            'services' => $this->availableServices(),
        ]);
    }

    public function store(StoreJobCardRequest $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();
        $serviceIds = $validated['service_ids'];
        unset($validated['service_ids']);

        $jobCard = JobCard::create($this->withBranchId(array_merge($validated, [
            'created_by' => $request->user()->id,
        ])));
        $this->syncJobCardServices($jobCard, $serviceIds);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Job card created.',
                'redirect' => route('job-cards.live'),
            ], 201);
        }

        return redirect()->route('job-cards.live')
            ->with('success', 'Job card created.');
    }

    public function show(JobCard $jobCard): View
    {
        $jobCard->load([
            'customer',
            'vehicle',
            'assignee',
            'creator',
            'booking',
            'services.service',
            'products.product',
            'checklistItems' => fn ($query) => $query->orderBy('sort_order'),
            'invoice.receipt',
        ]);

        $servicesTotal = (float) $jobCard->services->sum('price');
        $productsTotal = (float) $jobCard->products->sum(
            fn ($line) => (float) $line->quantity * (float) $line->unit_price
        );

        $commission = Commission::query()
            ->where('reference_type', $jobCard->getMorphClass())
            ->where('reference_id', $jobCard->id)
            ->first();

        $washDurationMinutes = null;
        if ($jobCard->started_at && $jobCard->completed_at) {
            $washDurationMinutes = (int) $jobCard->started_at->diffInMinutes($jobCard->completed_at);
        } elseif ($jobCard->started_at) {
            $washDurationMinutes = (int) $jobCard->started_at->diffInMinutes(now());
        }

        return view('job-cards.show', [
            'jobCard' => $jobCard,
            'servicesTotal' => $servicesTotal,
            'productsTotal' => $productsTotal,
            'grandTotal' => $servicesTotal + $productsTotal,
            'commission' => $commission,
            'washDurationMinutes' => $washDurationMinutes,
        ]);
    }

    public function edit(JobCard $jobCard): View
    {
        $vehicles = Vehicle::query()->with('customer')->get();

        return view('job-cards.edit', [
            'jobCard' => $jobCard,
            'customers' => Customer::query()->orderBy('full_name')->get(),
            'vehicles' => $vehicles,
            'bookings' => Booking::query()
                ->linkableToJobCard($jobCard->booking_id)
                ->with('customer')
                ->latest('scheduled_at')
                ->limit(50)
                ->get(),
            'employees' => $this->assignableEmployees(),
            'services' => $this->availableServices(),
            'selectedServiceIds' => $jobCard->services()->pluck('service_id')->all(),
        ]);
    }

    public function update(UpdateJobCardRequest $request, JobCard $jobCard, CommissionService $commissionService): RedirectResponse
    {
        $validated = $request->validated();
        $serviceIds = $validated['service_ids'] ?? null;
        unset($validated['service_ids']);

        $jobCard->update($this->statusAwarePayload($jobCard, $validated));
        $jobCard->refresh();

        if ($jobCard->status === JobCardStatus::Completed) {
            $commissionService->recordForJobCard($jobCard, CommissionSettings::TRIGGER_JOB_COMPLETED);
        }

        if (is_array($serviceIds)) {
            $this->syncJobCardServices($jobCard, $serviceIds);
        }

        return redirect()->route('job-cards.show', $jobCard)
            ->with('success', 'Job card updated.');
    }

    public function destroy(JobCard $jobCard): RedirectResponse
    {
        $jobCard->delete();

        return redirect()->route('job-cards.index')
            ->with('success', 'Job card deleted.');
    }

    public function open(): RedirectResponse
    {
        return redirect()->route('job-cards.index', ['section' => 'open']);
    }

    public function inProgress(): RedirectResponse
    {
        return redirect()->route('job-cards.index', ['section' => 'in_progress']);
    }

    public function completed(): RedirectResponse
    {
        return redirect()->route('job-cards.index', ['section' => 'completed']);
    }

    public function live(): View
    {
        $today = today();

        $jobCards = JobCard::query()
            ->forDay($today)
            ->with(['customer', 'vehicle', 'assignee', 'services.service', 'products.product'])
            ->whereIn('status', [JobCardStatus::Open, JobCardStatus::InProgress])
            ->latest()
            ->get();

        $posService = app(PosService::class);

        return view('job-cards.live', [
            'jobCards' => $jobCards,
            'jobCardsJson' => $jobCards->map(fn (JobCard $jobCard) => $this->liveJobCardPayload($jobCard, $posService))->values(),
            'stats' => [
                'total' => $jobCards->count(),
                'open' => $jobCards->where('status', JobCardStatus::Open)->count(),
                'in_progress' => $jobCards->where('status', JobCardStatus::InProgress)->count(),
                'unassigned' => $jobCards->whereNull('assigned_to')->count(),
            ],
        ]);
    }

    public function updateLiveStatus(UpdateJobCardRequest $request, JobCard $jobCard, CommissionService $commissionService): RedirectResponse|JsonResponse
    {
        $jobCard->update($this->statusAwarePayload($jobCard, $request->validated()));
        $jobCard->refresh();

        if ($jobCard->status === JobCardStatus::Completed) {
            $commissionService->recordForJobCard($jobCard, CommissionSettings::TRIGGER_JOB_COMPLETED);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Car washing status updated.',
                'remove_from_live' => ! in_array($jobCard->status, [JobCardStatus::Open, JobCardStatus::InProgress], true),
                'redirect_to_pos' => $jobCard->status === JobCardStatus::Completed
                    ? route('pos.index', ['job_card' => $jobCard->id])
                    : null,
                'job_card' => [
                    'id' => $jobCard->id,
                    'status' => $jobCard->status?->value ?? JobCardStatus::Open->value,
                    'started_at_human' => $jobCard->started_at?->diffForHumans(),
                    'completed_at_human' => $jobCard->completed_at?->diffForHumans(),
                ],
            ]);
        }

        if ($jobCard->status === JobCardStatus::Completed) {
            return redirect()->route('pos.index', ['job_card' => $jobCard->id])
                ->with('success', 'Vehicle marked ready. Complete checkout below.');
        }

        return back()->with('success', 'Car washing status updated.');
    }

    protected function liveJobCardPayload(JobCard $jobCard, PosService $posService): array
    {
        return [
            'id' => $jobCard->id,
            'registration_number' => $jobCard->vehicle?->registration_number ?? 'No vehicle assigned',
            'vehicle_summary' => trim(implode(' ', array_filter([
                $jobCard->vehicle?->make,
                $jobCard->vehicle?->model,
            ]))) ?: 'Vehicle details unavailable',
            'customer_name' => $jobCard->customer?->full_name ?? 'Walk-in',
            'assignee_name' => $jobCard->assignee?->displayName() ?? 'Unassigned',
            'status' => $jobCard->status?->value ?? JobCardStatus::Open->value,
            'started_at_human' => $jobCard->started_at?->diffForHumans(),
            'services_summary' => $jobCard->services
                ->map(fn ($line) => $line->service?->name)
                ->filter()
                ->join(', ') ?: 'No services selected',
            'view_url' => route('job-cards.show', $jobCard),
            'update_url' => route('job-cards.live-status', $jobCard),
            'pos_redirect_url' => route('pos.index', ['job_card' => $jobCard->id]),
            'pos_cart' => $posService->buildCartFromJobCard($jobCard),
        ];
    }

    protected function assignableEmployees()
    {
        return Employee::query()
            ->assignableToJobCards(session('current_branch_id'))
            ->get();
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
}
