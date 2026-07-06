<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AssignsBranchId;
use App\Http\Controllers\Concerns\SyncsJobCardServices;
use App\Http\Requests\StoreJobCardRequest;
use App\Http\Requests\UpdateJobCardRequest;
use App\Enums\JobCardStatus;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\JobCard;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class JobCardController extends Controller
{
    use AssignsBranchId, SyncsJobCardServices;

    public function index(): View
    {
        $today = today();
        $query = fn () => JobCard::query()->forDay($today)->with(['customer', 'vehicle', 'assignee'])->latest();
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
                'redirect' => route('job-cards.live'),
            ], 201);
        }

        return redirect()->route('job-cards.live')
            ->with('success', 'Job card created.');
    }

    public function show(JobCard $jobCard): View
    {
        return view('job-cards.show', [
            'jobCard' => $jobCard->load(['customer', 'vehicle', 'assignee', 'services.service', 'products', 'checklistItems']),
        ]);
    }

    public function edit(JobCard $jobCard): View
    {
        $vehicles = Vehicle::query()->with('customer')->get();

        return view('job-cards.edit', [
            'jobCard' => $jobCard,
            'customers' => Customer::query()->orderBy('full_name')->get(),
            'vehicles' => $vehicles,
            'bookings' => Booking::query()->with('customer')->latest()->limit(50)->get(),
            'employees' => $this->assignableEmployees(),
            'services' => $this->availableServices(),
            'selectedServiceIds' => $jobCard->services()->pluck('service_id')->all(),
        ]);
    }

    public function update(UpdateJobCardRequest $request, JobCard $jobCard): RedirectResponse
    {
        $validated = $request->validated();
        $serviceIds = $validated['service_ids'] ?? null;
        unset($validated['service_ids']);

        $jobCard->update($this->statusAwarePayload($jobCard, $validated));

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
            ->with(['customer', 'vehicle', 'assignee', 'services.service'])
            ->whereIn('status', [JobCardStatus::Open, JobCardStatus::InProgress])
            ->latest()
            ->get();

        return view('job-cards.live', [
            'jobCards' => $jobCards,
            'stats' => [
                'total' => $jobCards->count(),
                'open' => $jobCards->where('status', JobCardStatus::Open)->count(),
                'in_progress' => $jobCards->where('status', JobCardStatus::InProgress)->count(),
                'unassigned' => $jobCards->whereNull('assigned_to')->count(),
            ],
        ]);
    }

    public function updateLiveStatus(UpdateJobCardRequest $request, JobCard $jobCard): RedirectResponse|JsonResponse
    {
        $jobCard->update($this->statusAwarePayload($jobCard, $request->validated()));
        $jobCard->refresh();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Car washing status updated.',
                'remove_from_live' => ! in_array($jobCard->status, [JobCardStatus::Open, JobCardStatus::InProgress], true),
                'job_card' => [
                    'id' => $jobCard->id,
                    'status' => $jobCard->status?->value ?? JobCardStatus::Open->value,
                    'started_at_human' => $jobCard->started_at?->diffForHumans(),
                    'completed_at_human' => $jobCard->completed_at?->diffForHumans(),
                ],
            ]);
        }

        return back()->with('success', 'Car washing status updated.');
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
