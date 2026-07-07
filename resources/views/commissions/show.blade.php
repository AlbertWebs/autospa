@php
    use App\Services\CommissionService;
    use App\Support\RouteAccess;

    $employee = $commission->employee;
    $isPending = $commission->status === CommissionService::STATUS_PENDING;
    $isPaid = $commission->status === CommissionService::STATUS_PAID;
    $statusClass = 'asp-status-pill asp-status-pill--' . ($isPaid ? 'completed' : 'pending');
    $ratePercent = $commission->rate ? rtrim(rtrim(number_format($commission->rate * 100, 2), '0'), '.') : null;
    $canPay = $isPending && app(RouteAccess::class)->allows(auth()->user(), route('commissions.pay'), 'POST');
    $commissionsDayUrl = route('commissions.index', ['date' => $commission->earned_on?->toDateString() ?? now()->toDateString()]);
@endphp

<x-layouts.app>
    <x-slot name="header"><span class="hidden sm:inline">Staff</span></x-slot>

    <div
        x-data="commissionMpesaPayout({
            initiateUrl: @js(route('commissions.pay.mpesa.initiate')),
            confirmUrl: @js(route('commissions.pay.mpesa.confirm')),
            csrfToken: @js(csrf_token()),
        })"
    >
    <x-ui.section-header eyebrow="Staff">
        <a href="{{ $commissionsDayUrl }}" class="asp-btn asp-btn-secondary">
            <span class="material-symbols-outlined text-lg">arrow_back</span>
            Daily Commissions
        </a>
        @if ($employee && app(RouteAccess::class)->allows(auth()->user(), route('employees.show', $employee)))
            <a href="{{ route('employees.show', $employee) }}" class="asp-btn asp-btn-secondary">
                <span class="material-symbols-outlined text-lg">person</span>
                View Washer
            </a>
        @endif
        @if ($jobCard && app(RouteAccess::class)->allows(auth()->user(), route('job-cards.show', $jobCard)))
            <a href="{{ route('job-cards.show', $jobCard) }}" class="asp-btn asp-btn-secondary">
                <span class="material-symbols-outlined text-lg">garage</span>
                View Job Card
            </a>
        @endif
        @if ($canPay)
            <form method="POST" action="{{ route('commissions.pay') }}">
                @csrf
                <input type="hidden" name="employee_id" value="{{ $commission->employee_id }}">
                <input type="hidden" name="date" value="{{ $commission->earned_on?->toDateString() }}">
                <button type="submit" class="asp-btn asp-btn-primary">
                    <span class="material-symbols-outlined text-lg">payments</span>
                    Mark Paid
                </button>
            </form>
            @if ($employee?->phone)
                <button
                    type="button"
                    class="asp-btn asp-btn-primary !bg-emerald-600 hover:!bg-emerald-500"
                    @click="startMpesaPayout({{ $commission->employee_id }}, @js($commission->earned_on?->toDateString()), @js($employee->full_name), {{ (float) $commission->amount }})"
                >
                    <span class="material-symbols-outlined text-lg">phone_iphone</span>
                    Send M-Pesa
                </button>
            @endif
        @endif
    </x-ui.section-header>

    <div class="asp-detail-hero">
        <div class="asp-detail-hero-body">
            <div>
                <p class="asp-detail-time">KES {{ number_format($commission->amount ?? 0, 0) }}</p>
                <p class="asp-detail-date">
                    Commission for {{ $employee?->full_name ?? 'Unknown washer' }}
                </p>
                @if ($commission->earned_on)
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                        <span class="material-symbols-outlined mr-1 align-middle text-base">event</span>
                        Earned {{ $commission->earned_on->format('l, F j, Y') }}
                    </p>
                @endif
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <span class="{{ $statusClass }}">
                    <span class="h-1.5 w-1.5 rounded-full bg-current opacity-70"></span>
                    {{ ucfirst($commission->status ?? 'pending') }}
                </span>
                @if ($ratePercent)
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200/80 bg-white px-3 py-1 text-xs font-medium text-slate-600 dark:border-brand-border/60 dark:bg-brand-surface-high dark:text-slate-300">
                        <span class="material-symbols-outlined text-sm">percent</span>
                        {{ $ratePercent }}% of wash
                    </span>
                @endif
            </div>
        </div>
    </div>

    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <x-ui.stat-card
            variant="revenue"
            label="Wash Value"
            icon="local_car_wash"
            :value="$baseAmount ? 'KES ' . number_format($baseAmount, 0) : '—'"
            hint="Service total before commission"
        />
        <x-ui.stat-card
            variant="service"
            label="Commission Rate"
            icon="percent"
            :value="$ratePercent ? $ratePercent . '%' : '—'"
            hint="Company default per wash"
        />
        <x-ui.stat-card
            variant="payments"
            label="Commission Amount"
            icon="payments"
            :value="'KES ' . number_format($commission->amount ?? 0, 0)"
            :hint="$isPending ? 'Awaiting daily payout' : 'Paid to washer'"
        />
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="asp-panel">
            <div class="asp-panel-header">
                <h2 class="asp-panel-title">Washer</h2>
                <span class="material-symbols-outlined text-brand-primary-dim dark:text-brand-primary">engineering</span>
            </div>
            <div class="asp-panel-body">
                <dl class="asp-detail-dl">
                    <div>
                        <dt class="asp-detail-dt">Name</dt>
                        <dd class="asp-detail-dd">
                            @if ($employee)
                                <a href="{{ route('employees.show', $employee) }}" class="asp-detail-link">
                                    {{ $employee->full_name }}
                                    <span class="material-symbols-outlined text-sm">open_in_new</span>
                                </a>
                            @else
                                <span class="text-slate-400">Not available</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="asp-detail-dt">Role</dt>
                        <dd class="asp-detail-dd">{{ $employee?->typeLabel() ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="asp-detail-dt">Phone</dt>
                        <dd class="asp-detail-dd">
                            @if ($employee?->phone)
                                <a href="tel:{{ $employee->phone }}" class="asp-detail-link">{{ $employee->phone }}</a>
                            @else
                                <span class="text-slate-400">Not set</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="asp-panel">
            <div class="asp-panel-header">
                <h2 class="asp-panel-title">Payout</h2>
                <span class="material-symbols-outlined text-brand-primary-dim dark:text-brand-primary">account_balance_wallet</span>
            </div>
            <div class="asp-panel-body">
                <dl class="asp-detail-dl">
                    <div>
                        <dt class="asp-detail-dt">Status</dt>
                        <dd class="asp-detail-dd">
                            <x-ui.badge :color="$isPaid ? 'emerald' : 'indigo'">
                                {{ ucfirst($commission->status ?? 'pending') }}
                            </x-ui.badge>
                        </dd>
                    </div>
                    <div>
                        <dt class="asp-detail-dt">Triggered by</dt>
                        <dd class="asp-detail-dd">{{ $triggerLabel }}</dd>
                    </div>
                    @if ($isPaid)
                        <div>
                            <dt class="asp-detail-dt">Paid at</dt>
                            <dd class="asp-detail-dd">{{ $commission->paid_at?->format('M j, Y g:i A') ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="asp-detail-dt">Payment method</dt>
                            <dd class="asp-detail-dd">{{ ucfirst($commission->payment_method ?? 'manual') }}</dd>
                        </div>
                        @if ($commission->payment_reference)
                            <div>
                                <dt class="asp-detail-dt">Reference</dt>
                                <dd class="asp-detail-dd font-mono text-xs">{{ $commission->payment_reference }}</dd>
                            </div>
                        @endif
                    @else
                        <div>
                            <dt class="asp-detail-dt">Settlement</dt>
                            <dd class="asp-detail-dd text-amber-600 dark:text-amber-400">
                                Pending — settle from
                                <a href="{{ $commissionsDayUrl }}" class="asp-detail-link">Daily Commissions</a>
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>
    </div>

    @if ($jobCard)
        <div class="asp-panel mt-6">
            <div class="asp-panel-header">
                <div>
                    <h2 class="asp-panel-title">Linked Wash</h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                        Job card #{{ $jobCard->id }}
                        @if ($jobCard->completed_at)
                            · completed {{ $jobCard->completed_at->format('M j, Y g:i A') }}
                        @endif
                    </p>
                </div>
                <span class="material-symbols-outlined text-brand-primary-dim dark:text-brand-primary">garage</span>
            </div>
            <div class="asp-panel-body">
                <dl class="asp-detail-dl mb-6">
                    <div>
                        <dt class="asp-detail-dt">Customer</dt>
                        <dd class="asp-detail-dd">
                            @if ($jobCard->customer)
                                <a href="{{ route('customers.show', $jobCard->customer) }}" class="asp-detail-link">
                                    {{ $jobCard->customer->full_name }}
                                    <span class="material-symbols-outlined text-sm">open_in_new</span>
                                </a>
                            @else
                                <span class="text-slate-400">Walk-in</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="asp-detail-dt">Vehicle</dt>
                        <dd class="asp-detail-dd">
                            @if ($jobCard->vehicle)
                                <a href="{{ route('vehicles.show', $jobCard->vehicle) }}" class="asp-detail-link">
                                    {{ $jobCard->vehicle->registration_number }}
                                    <span class="material-symbols-outlined text-sm">open_in_new</span>
                                </a>
                            @else
                                <span class="text-slate-400">Not set</span>
                            @endif
                        </dd>
                    </div>
                </dl>

                @if ($jobCard->services->isNotEmpty())
                    <div class="space-y-2">
                        @foreach ($jobCard->services as $line)
                            <div @class(['asp-service-row', 'mb-2' => ! $loop->last])>
                                <div class="flex min-w-0 items-center gap-3">
                                    <span class="asp-service-row-icon">
                                        <span class="material-symbols-outlined text-lg">local_car_wash</span>
                                    </span>
                                    <p class="truncate font-medium text-slate-900 dark:text-white">
                                        {{ $line->service?->name ?? 'Service' }}
                                    </p>
                                </div>
                                @if ($line->price)
                                    <p class="shrink-0 font-mono text-sm font-semibold text-slate-900 dark:text-white">
                                        KES {{ number_format($line->price, 0) }}
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif

    <footer class="asp-meta-line mt-6">
        <span>Commission #{{ $commission->id }}</span>
        @if ($jobCard)
            <span>Job card #{{ $jobCard->id }}</span>
        @endif
        <a href="{{ $commissionsDayUrl }}" class="asp-detail-link">Back to daily list</a>
    </footer>

    @include('commissions._mpesa-otp-modal')
    </div>
</x-layouts.app>
