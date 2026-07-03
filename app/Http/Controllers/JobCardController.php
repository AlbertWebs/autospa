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
                'redirect' => route('job-cards.show', $jobCard),
            ], 201);
        }

        return redirect()->route('job-cards.show', $jobCard)
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
        $jobCard->update($request->validated());

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

    public function completed(): View
    {
        return $this->listByStatus(JobCardStatus::Completed, 'job-cards.completed');
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
}
