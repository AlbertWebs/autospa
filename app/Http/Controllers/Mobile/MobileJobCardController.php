<?php

namespace App\Http\Controllers\Mobile;

use App\Enums\JobCardStatus;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\AssignsBranchId;
use App\Http\Requests\StoreJobCardRequest;
use App\Http\Requests\UpdateJobCardRequest;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\JobCard;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MobileJobCardController extends Controller
{
    use AssignsBranchId;

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
            ->with(['customer', 'vehicle', 'assignee'])
            ->whereIn('status', [JobCardStatus::Open, JobCardStatus::InProgress])
            ->latest()
            ->get();

        return view('mobile.job-cards.live', [
            'jobCards' => $jobCards,
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
            'jobCard' => $jobCard->load(['customer', 'vehicle', 'assignee', 'services', 'products']),
        ]);
    }

    public function create(): View
    {
        return view('mobile.job-cards.create', [
            'customers' => Customer::query()->orderBy('full_name')->get(),
            'vehicles' => Vehicle::query()->with('customer')->get(),
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
            ]);
        }

        return back()->with('success', 'Status updated.');
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
