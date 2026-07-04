<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AssignsBranchId;
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
    use AssignsBranchId;

    public function index(): View
    {
        return view('job-cards.index', [
            'jobCards' => JobCard::query()->with(['customer', 'vehicle', 'assignee'])->latest()->paginate(15),
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
        ]);
    }

    public function store(StoreJobCardRequest $request): RedirectResponse|JsonResponse
    {
        $jobCard = JobCard::create($this->withBranchId($request->validated()));

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
            'jobCard' => $jobCard->load(['customer', 'vehicle', 'assignee', 'services', 'products', 'checklistItems']),
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
        ]);
    }

    public function update(UpdateJobCardRequest $request, JobCard $jobCard): RedirectResponse
    {
        $jobCard->update($this->statusAwarePayload($jobCard, $request->validated()));

        return redirect()->route('job-cards.show', $jobCard)
            ->with('success', 'Job card updated.');
    }

    public function destroy(JobCard $jobCard): RedirectResponse
    {
        $jobCard->delete();

        return redirect()->route('job-cards.index')
            ->with('success', 'Job card deleted.');
    }

    public function open(): View
    {
        return $this->listByStatus(JobCardStatus::Open, 'job-cards.open');
    }

    public function inProgress(): View
    {
        return $this->listByStatus(JobCardStatus::InProgress, 'job-cards.in-progress');
    }

    public function live(): View
    {
        $jobCards = JobCard::query()
            ->with(['customer', 'vehicle', 'assignee'])
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

    public function completed(): View
    {
        return $this->listByStatus(JobCardStatus::Completed, 'job-cards.completed');
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

    protected function listByStatus(JobCardStatus $status, string $view): View
    {
        return view($view, [
            'jobCards' => JobCard::query()
                ->with(['customer', 'vehicle', 'assignee'])
                ->where('status', $status)
                ->latest()
                ->paginate(15),
        ]);
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
