<?php

namespace App\Http\Controllers\Mobile;

use App\Enums\JobCardStatus;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\AssignsBranchId;
use App\Http\Controllers\Concerns\SyncsJobCardServices;
use App\Http\Requests\StoreJobCardRequest;
use App\Http\Requests\UpdateJobCardRequest;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\JobCard;
use App\Models\Vehicle;
use App\Services\PosService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MobileJobCardController extends Controller
{
    use AssignsBranchId, SyncsJobCardServices;

    public function index(Request $request): View
    {
        $today = today();
        $section = $request->string('section', 'open')->toString();

        $status = match ($section) {
            'in_progress' => JobCardStatus::InProgress,
            'completed' => JobCardStatus::Completed,
            default => JobCardStatus::Open,
        };

        $jobCards = JobCard::query()
            ->forDay($today)
            ->with(['customer', 'vehicle', 'assignee'])
            ->where('status', $status)
            ->latest()
            ->get();

        return view('mobile.job-cards.index', [
            'section' => $section,
            'jobCards' => $jobCards,
            'counts' => [
                'open' => JobCard::query()->forDay($today)->where('status', JobCardStatus::Open)->count(),
                'in_progress' => JobCard::query()->forDay($today)->where('status', JobCardStatus::InProgress)->count(),
                'completed' => JobCard::query()->forDay($today)->where('status', JobCardStatus::Completed)->count(),
            ],
        ]);
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

        return view('mobile.job-cards.live', [
            'jobCards' => $jobCards,
            'jobCardsJson' => $jobCards->map(fn (JobCard $jobCard) => $this->liveJobCardPayload($jobCard, $posService))->values(),
            'stats' => [
                'total' => $jobCards->count(),
                'open' => $jobCards->where('status', JobCardStatus::Open)->count(),
                'in_progress' => $jobCards->where('status', JobCardStatus::InProgress)->count(),
                'unassigned' => $jobCards->whereNull('assigned_to')->count(),
            ],
            'canManage' => auth()->user()?->hasAnyPermission(['job-cards.manage']) ?? false,
        ]);
    }

    public function show(JobCard $jobCard): View
    {
        return view('mobile.job-cards.show', [
            'jobCard' => $jobCard->load(['customer', 'vehicle', 'assignee', 'services.service', 'products']),
        ]);
    }

    public function create(): View
    {
        return view('mobile.job-cards.create', [
            'customers' => Customer::query()->orderBy('full_name')->get(),
            'vehicles' => Vehicle::query()->with('customer')->get(),
            'bookings' => Booking::query()->with('customer')->latest()->limit(50)->get(),
            'employees' => $this->assignableEmployees(),
            'services' => $this->availableServices(),
        ]);
    }

    public function store(StoreJobCardRequest $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();
        $serviceIds = $validated['service_ids'];
        unset($validated['service_ids']);

        $jobCard = JobCard::create($this->withBranchId($validated));
        $this->syncJobCardServices($jobCard, $serviceIds);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Job card created.',
                'redirect' => route('mobile.job-cards.live'),
            ], 201);
        }

        return redirect()->route('mobile.job-cards.live')
            ->with('success', 'Job card created.');
    }

    public function updateLiveStatus(UpdateJobCardRequest $request, JobCard $jobCard): RedirectResponse|JsonResponse
    {
        $jobCard->update($this->statusAwarePayload($jobCard, $request->validated()));
        $jobCard->refresh();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Status updated.',
                'remove_from_live' => ! in_array($jobCard->status, [JobCardStatus::Open, JobCardStatus::InProgress], true),
                'redirect_to_pos' => $jobCard->status === JobCardStatus::Completed
                    ? route('mobile.pos.index', ['job_card' => $jobCard->id])
                    : null,
            ]);
        }

        if ($jobCard->status === JobCardStatus::Completed) {
            return redirect()->route('mobile.pos.index', ['job_card' => $jobCard->id])
                ->with('success', 'Vehicle marked ready. Complete checkout below.');
        }

        return back()->with('success', 'Status updated.');
    }

    protected function liveJobCardPayload(JobCard $jobCard, PosService $posService): array
    {
        return [
            'id' => $jobCard->id,
            'registration_number' => $jobCard->vehicle?->registration_number ?? 'No vehicle',
            'vehicle_summary' => trim(implode(' ', array_filter([$jobCard->vehicle?->make, $jobCard->vehicle?->model]))) ?: '—',
            'customer_name' => $jobCard->customer?->full_name ?? 'Walk-in',
            'assignee_name' => $jobCard->assignee?->displayName() ?? 'Unassigned',
            'status' => $jobCard->status?->value ?? JobCardStatus::Open->value,
            'started_at_human' => $jobCard->started_at?->diffForHumans(),
            'services_summary' => $jobCard->services
                ->map(fn ($line) => $line->service?->name)
                ->filter()
                ->join(', ') ?: 'No services selected',
            'view_url' => route('mobile.job-cards.show', $jobCard),
            'update_url' => route('mobile.job-cards.live-status', $jobCard),
            'pos_redirect_url' => route('mobile.pos.index', ['job_card' => $jobCard->id]),
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
