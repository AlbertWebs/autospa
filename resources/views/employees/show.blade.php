@php
    $initials = collect(explode(' ', $employee->full_name))
        ->filter()
        ->map(fn (string $part) => strtoupper(substr($part, 0, 1)))
        ->take(2)
        ->join('');
@endphp

<x-layouts.app>
    <x-slot name="header"><span class="hidden sm:inline">Staff</span></x-slot>

    <x-ui.section-header eyebrow="Staff">
        <a href="{{ route('employees.index') }}" class="asp-btn asp-btn-secondary">
            <span class="material-symbols-outlined text-lg">arrow_back</span>
            Back
        </a>
        <a href="{{ route('employees.edit', $employee) }}" class="asp-btn asp-btn-primary">
            <span class="material-symbols-outlined text-lg">edit</span>
            Edit
        </a>
        <form method="POST" action="{{ route('employees.destroy', $employee) }}" onsubmit="return confirm('Delete this employee?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="asp-btn asp-btn-danger">
                <span class="material-symbols-outlined text-lg">delete</span>
                Delete
            </button>
        </form>
    </x-ui.section-header>

    <div class="asp-detail-hero">
        <div class="asp-detail-hero-body">
            <div class="flex min-w-0 flex-1 items-center gap-5">
                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500/25 to-brand-primary/10 text-xl font-bold text-brand-primary-dim ring-2 ring-white dark:text-brand-primary dark:ring-brand-surface-high">
                    {{ $initials }}
                </div>
                <div class="min-w-0">
                    <h1 class="truncate font-display text-2xl font-bold text-slate-900 dark:text-white sm:text-3xl">
                        {{ $employee->full_name }}
                    </h1>
                    <p class="mt-1 font-mono text-sm text-slate-500 dark:text-slate-400">
                        {{ $employee->employee_number }}
                        @if ($employee->position)
                            · {{ $employee->position }}
                        @endif
                    </p>
                    @if ($employee->hire_date)
                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                            <span class="material-symbols-outlined mr-1 align-middle text-base">event</span>
                            Joined {{ $employee->hire_date->format('M j, Y') }}
                        </p>
                    @endif
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200/80 bg-white px-3 py-1 text-xs font-semibold text-slate-700 dark:border-brand-border/60 dark:bg-brand-surface-high dark:text-slate-200">
                    <span class="material-symbols-outlined text-sm">{{ $employee->isSupervisor() ? 'supervisor_account' : 'engineering' }}</span>
                    {{ $employee->typeLabel() }}
                </span>
                @if ($employee->is_active)
                    <x-ui.badge color="green">Active</x-ui.badge>
                @else
                    <x-ui.badge color="slate">Inactive</x-ui.badge>
                @endif
            </div>
        </div>
    </div>

    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat-card variant="service" label="Active Jobs" icon="build" :value="$stats['active_jobs']" hint="Open or in progress" />
        <x-ui.stat-card variant="ready" label="Completed" icon="task_alt" :value="$stats['completed_jobs']" hint="All time" />
        @if ($commissionsEnabled && $employee->isAttendee())
            <x-ui.stat-card variant="payments" label="Commission Earned" icon="payments" :value="'KES ' . number_format($stats['commission_earned'], 0)" />
            <x-ui.stat-card variant="revenue" label="Pending Payout" icon="schedule" :value="'KES ' . number_format($stats['commission_pending'], 0)" />
        @elseif ($employee->isSupervisor())
            <x-ui.stat-card variant="payments" label="Base Salary" icon="account_balance_wallet" :value="'KES ' . number_format($employee->base_salary ?? 0, 0)" hint="Monthly" />
        @endif
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="asp-panel">
            <div class="asp-panel-header">
                <h2 class="asp-panel-title">Contact</h2>
                <span class="material-symbols-outlined text-brand-primary-dim dark:text-brand-primary">contact_page</span>
            </div>
            <div class="asp-panel-body">
                <dl class="asp-detail-dl">
                    <div>
                        <dt class="asp-detail-dt">Phone</dt>
                        <dd class="asp-detail-dd">
                            @if ($employee->phone)
                                <a href="tel:{{ $employee->phone }}" class="asp-detail-link">{{ $employee->phone }}</a>
                            @else
                                <span class="text-slate-400">Not set</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="asp-detail-dt">Email</dt>
                        <dd class="asp-detail-dd">
                            @if ($employee->email)
                                <a href="mailto:{{ $employee->email }}" class="asp-detail-link">{{ $employee->email }}</a>
                            @else
                                <span class="text-slate-400">Not set</span>
                            @endif
                        </dd>
                    </div>
                    @if ($employee->user)
                        <div>
                            <dt class="asp-detail-dt">System User</dt>
                            <dd class="asp-detail-dd">
                                <a href="{{ route('settings.users.show', $employee->user) }}" class="asp-detail-link">
                                    {{ $employee->user->name }}
                                    <span class="material-symbols-outlined text-sm">open_in_new</span>
                                </a>
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>

        <div class="asp-panel">
            <div class="asp-panel-header">
                <h2 class="asp-panel-title">Compensation</h2>
                <span class="material-symbols-outlined text-brand-primary-dim dark:text-brand-primary">payments</span>
            </div>
            <div class="asp-panel-body">
                <dl class="asp-detail-dl">
                    @if ($employee->isSupervisor())
                        <div>
                            <dt class="asp-detail-dt">Pay structure</dt>
                            <dd class="asp-detail-dd">Fixed monthly salary</dd>
                        </div>
                        <div>
                            <dt class="asp-detail-dt">Base salary</dt>
                            <dd class="asp-detail-dd">KES {{ number_format($employee->base_salary ?? 0, 0) }}</dd>
                        </div>
                    @else
                        <div>
                            <dt class="asp-detail-dt">Pay structure</dt>
                            <dd class="asp-detail-dd">Commission per wash</dd>
                        </div>
                        @if ($commissionsEnabled)
                            <div>
                                <dt class="asp-detail-dt">Commission rate</dt>
                                <dd class="asp-detail-dd">{{ rtrim(rtrim(number_format($commissionRatePercent, 2), '0'), '.') }}%</dd>
                            </div>
                        @endif
                    @endif
                    <div>
                        <dt class="asp-detail-dt">Hire date</dt>
                        <dd class="asp-detail-dd">{{ $employee->hire_date?->format('M j, Y') ?? 'Not set' }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <x-ui.panel title="Recent Job Cards">
            <div class="overflow-x-auto">
                <table class="asp-table w-full">
                    <thead>
                        <tr>
                            <x-ui.th>Vehicle</x-ui.th>
                            <x-ui.th>Customer</x-ui.th>
                            <x-ui.th>Status</x-ui.th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employee->assignedJobCards as $jobCard)
                            <tr class="asp-table-row">
                                <x-ui.td>
                                    <a href="{{ route('job-cards.show', $jobCard) }}" class="asp-detail-link font-medium">
                                        {{ $jobCard->vehicle?->registration_number ?? '—' }}
                                    </a>
                                </x-ui.td>
                                <x-ui.td muted>{{ $jobCard->customer?->full_name ?? 'Walk-in' }}</x-ui.td>
                                <x-ui.td>
                                    <span class="asp-status-pill asp-status-pill--{{ $jobCard->status->value }}">
                                        {{ $jobCard->status->label() }}
                                    </span>
                                </x-ui.td>
                            </tr>
                        @empty
                            <tr>
                                <x-ui.td colspan="3" muted>No job cards assigned yet.</x-ui.td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.panel>

        @if ($commissionsEnabled && $employee->isAttendee())
            <x-ui.panel title="Recent Commissions">
                <div class="overflow-x-auto">
                    <table class="asp-table w-full">
                        <thead>
                            <tr>
                                <x-ui.th>Date</x-ui.th>
                                <x-ui.th>Amount</x-ui.th>
                                <x-ui.th>Status</x-ui.th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($employee->commissions as $commission)
                                <tr class="asp-table-row">
                                    <x-ui.td muted>{{ $commission->earned_on?->format('M j, Y') ?? '—' }}</x-ui.td>
                                    <x-ui.td primary>KES {{ number_format($commission->amount, 0) }}</x-ui.td>
                                    <x-ui.td>
                                        <x-ui.badge :color="$commission->status === 'paid' ? 'green' : 'amber'">
                                            {{ ucfirst($commission->status) }}
                                        </x-ui.badge>
                                    </x-ui.td>
                                </tr>
                            @empty
                                <tr>
                                    <x-ui.td colspan="3" muted>No commissions recorded yet.</x-ui.td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ui.panel>
        @elseif ($attendanceEnabled && $employee->relationLoaded('attendance'))
            <x-ui.panel title="Recent Attendance">
                <div class="overflow-x-auto">
                    <table class="asp-table w-full">
                        <thead>
                            <tr>
                                <x-ui.th>Date</x-ui.th>
                                <x-ui.th>In</x-ui.th>
                                <x-ui.th>Out</x-ui.th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($employee->attendance as $record)
                                <tr class="asp-table-row">
                                    <x-ui.td>
                                        <a href="{{ route('attendance.show', $record) }}" class="asp-detail-link">
                                            {{ $record->date?->format('M j, Y') }}
                                        </a>
                                    </x-ui.td>
                                    <x-ui.td muted>{{ $record->clock_in ?? '—' }}</x-ui.td>
                                    <x-ui.td muted>{{ $record->clock_out ?? '—' }}</x-ui.td>
                                </tr>
                            @empty
                                <tr>
                                    <x-ui.td colspan="3" muted>No attendance records yet.</x-ui.td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ui.panel>
        @endif
    </div>
</x-layouts.app>
