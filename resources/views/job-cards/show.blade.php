@php
    use App\Enums\JobCardStatus;
    use App\Support\RouteAccess;

    $routeAccess = app(RouteAccess::class);
    $user = auth()->user();

    $statusPillClass = match ($jobCard->status) {
        JobCardStatus::Open => 'asp-status-pill--pending',
        JobCardStatus::InProgress => 'asp-status-pill--in_progress',
        JobCardStatus::Completed => 'asp-status-pill--completed',
        JobCardStatus::Cancelled => 'asp-status-pill--cancelled',
    };

    $canManage = $routeAccess->allowsUrl($user, route('job-cards.live-status', $jobCard), 'PATCH');
    $canEdit = $routeAccess->allowsUrl($user, route('job-cards.edit', $jobCard));
    $canDelete = $routeAccess->allowsUrl($user, route('job-cards.destroy', $jobCard), 'DELETE');
    $canCheckout = $routeAccess->allows($user, 'pos.index')
        && $jobCard->status === JobCardStatus::Completed
        && ! $jobCard->invoice;
    $canViewInvoice = $jobCard->invoice
        && $routeAccess->allows($user, 'invoices.show');
    $canViewCommission = $commission
        && $routeAccess->allows($user, 'commissions.show');

    $workflowSteps = [
        ['key' => 'open', 'label' => 'Queued', 'icon' => 'hourglass_top'],
        ['key' => 'in_progress', 'label' => 'Washing', 'icon' => 'local_car_wash'],
        ['key' => 'completed', 'label' => 'Ready', 'icon' => 'check_circle'],
    ];
    $currentStep = match ($jobCard->status) {
        JobCardStatus::Open => 0,
        JobCardStatus::InProgress => 1,
        JobCardStatus::Completed => 2,
        default => -1,
    };

    $checklistDone = $jobCard->checklistItems->where('is_completed', true)->count();
    $checklistTotal = $jobCard->checklistItems->count();
@endphp

<x-layouts.app>
    <x-slot name="header"><span class="hidden sm:inline">Operations</span></x-slot>

    <x-ui.section-header eyebrow="Operations">
        <a href="{{ route('job-cards.index') }}" class="asp-btn asp-btn-secondary">
            <span class="material-symbols-outlined text-lg">arrow_back</span>
            Job Cards
        </a>
        @if ($routeAccess->allows($user, 'job-cards.live'))
            <a href="{{ route('job-cards.live') }}" class="asp-btn asp-btn-secondary">
                <span class="material-symbols-outlined text-lg">sensors</span>
                Live Board
            </a>
        @endif
        @if ($canManage && $jobCard->status === JobCardStatus::Open)
            <form method="POST" action="{{ route('job-cards.live-status', $jobCard) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="{{ JobCardStatus::InProgress->value }}">
                <button type="submit" class="asp-btn asp-btn-primary">
                    <span class="material-symbols-outlined text-lg">play_arrow</span>
                    Start Wash
                </button>
            </form>
        @endif
        @if ($canManage && $jobCard->status === JobCardStatus::InProgress)
            <form method="POST" action="{{ route('job-cards.live-status', $jobCard) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="{{ JobCardStatus::Completed->value }}">
                <button type="submit" class="asp-btn asp-btn-primary !bg-emerald-600 hover:!bg-emerald-500">
                    <span class="material-symbols-outlined text-lg">check_circle</span>
                    Mark Ready
                </button>
            </form>
        @endif
        @if ($canCheckout)
            <a href="{{ route('pos.index', ['job_card' => $jobCard->id]) }}" class="asp-btn asp-btn-primary">
                <span class="material-symbols-outlined text-lg">point_of_sale</span>
                Checkout
            </a>
        @endif
        @if ($canEdit)
            <a href="{{ route('job-cards.edit', $jobCard) }}" class="asp-btn asp-btn-secondary">
                <span class="material-symbols-outlined text-lg">edit</span>
                Edit
            </a>
        @endif
        @if ($canDelete)
            <form method="POST" action="{{ route('job-cards.destroy', $jobCard) }}" onsubmit="return confirm('Delete this job card?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="asp-btn asp-btn-danger">
                    <span class="material-symbols-outlined text-lg">delete</span>
                    Delete
                </button>
            </form>
        @endif
    </x-ui.section-header>

    {{-- Hero: vehicle + status --}}
    <div class="asp-detail-hero">
        <div class="asp-detail-hero-body">
            <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Job Card #{{ $jobCard->id }}</p>
                <p class="asp-detail-time mt-1 font-mono tracking-wide">
                    {{ $jobCard->vehicle?->registration_number ?? 'No vehicle' }}
                </p>
                @if ($jobCard->vehicle && ($jobCard->vehicle->make || $jobCard->vehicle->model))
                    <p class="asp-detail-date">
                        {{ trim(implode(' ', array_filter([$jobCard->vehicle->make, $jobCard->vehicle->model, $jobCard->vehicle->color]))) }}
                    </p>
                @endif
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    <span class="material-symbols-outlined mr-1 align-middle text-base">person</span>
                    {{ $jobCard->customer?->full_name ?? 'Walk-in customer' }}
                    @if ($jobCard->assignee)
                        <span class="text-slate-300 dark:text-slate-600">·</span>
                        <span class="material-symbols-outlined mr-0.5 align-middle text-base">engineering</span>
                        {{ $jobCard->assignee->displayName() }}
                    @endif
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <span class="asp-status-pill {{ $statusPillClass }}">
                    <span class="h-1.5 w-1.5 rounded-full bg-current opacity-70"></span>
                    {{ $jobCard->status->label() }}
                </span>
                @if ($jobCard->started_at)
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200/80 bg-white px-3 py-1 text-xs font-medium text-slate-600 dark:border-brand-border/60 dark:bg-brand-surface-high dark:text-slate-300">
                        <span class="material-symbols-outlined text-sm">schedule</span>
                        Started {{ $jobCard->started_at->diffForHumans() }}
                    </span>
                @endif
                @if ($jobCard->completed_at)
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200/80 bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">
                        <span class="material-symbols-outlined text-sm">done_all</span>
                        Ready {{ $jobCard->completed_at->format('g:i A') }}
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- Wash progress --}}
    @if ($jobCard->status !== JobCardStatus::Cancelled && $currentStep >= 0)
        <div class="asp-panel mb-6">
            <div class="asp-panel-body py-5">
                <div class="flex items-center">
                    @foreach ($workflowSteps as $index => $step)
                        @php
                            $isDone = $index < $currentStep;
                            $isCurrent = $index === $currentStep;
                        @endphp
                        <div class="flex min-w-0 flex-1 items-center">
                            <div class="flex min-w-0 flex-col items-center gap-2 text-center sm:flex-row sm:gap-3 sm:text-left">
                                <div @class([
                                    'flex h-10 w-10 shrink-0 items-center justify-center rounded-full border-2 transition',
                                    'border-emerald-500 bg-emerald-500 text-white' => $isDone,
                                    'border-brand-primary bg-brand-primary/10 text-brand-primary-dim dark:text-brand-primary' => $isCurrent,
                                    'border-slate-200 bg-slate-50 text-slate-400 dark:border-brand-border/60 dark:bg-brand-surface/40' => ! $isDone && ! $isCurrent,
                                ])>
                                    <span class="material-symbols-outlined text-lg">{{ $isDone ? 'check' : $step['icon'] }}</span>
                                </div>
                                <div class="min-w-0">
                                    <p @class([
                                        'text-xs font-semibold uppercase tracking-wide',
                                        'text-emerald-600 dark:text-emerald-400' => $isDone,
                                        'text-brand-primary-dim dark:text-brand-primary' => $isCurrent,
                                        'text-slate-400' => ! $isDone && ! $isCurrent,
                                    ])>{{ $step['label'] }}</p>
                                </div>
                            </div>
                            @if (! $loop->last)
                                <div @class([
                                    'mx-2 h-0.5 min-w-[1rem] flex-1 rounded-full sm:mx-4',
                                    'bg-emerald-500' => $index < $currentStep,
                                    'bg-slate-200 dark:bg-brand-border/60' => $index >= $currentStep,
                                ])></div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- Stats --}}
    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat-card
            variant="revenue"
            label="Services"
            icon="design_services"
            :value="$servicesTotal > 0 ? 'KES ' . number_format($servicesTotal, 0) : '—'"
            :hint="$jobCard->services->count() . ' ' . Str::plural('service', $jobCard->services->count())"
        />
        <x-ui.stat-card
            variant="service"
            label="Products"
            icon="inventory_2"
            :value="$productsTotal > 0 ? 'KES ' . number_format($productsTotal, 0) : '—'"
            :hint="$jobCard->products->count() . ' ' . Str::plural('item', $jobCard->products->count())"
        />
        <x-ui.stat-card
            variant="payments"
            label="Job Total"
            icon="payments"
            :value="$grandTotal > 0 ? 'KES ' . number_format($grandTotal, 0) : '—'"
            hint="Services + products before checkout"
        />
        <x-ui.stat-card
            variant="default"
            label="Wash Time"
            icon="timer"
            :value="$washDurationMinutes !== null ? $washDurationMinutes . ' min' : '—'"
            :hint="$jobCard->completed_at ? 'Start to ready' : ($jobCard->started_at ? 'Elapsed so far' : 'Not started yet')"
        />
    </div>

    <div class="grid gap-6 lg:grid-cols-5">
        {{-- Customer, vehicle & links --}}
        <div class="asp-panel lg:col-span-2">
            <div class="asp-panel-header">
                <h2 class="asp-panel-title">Details</h2>
                <span class="material-symbols-outlined text-brand-primary-dim dark:text-brand-primary">info</span>
            </div>
            <div class="asp-panel-body">
                <dl class="asp-detail-dl">
                    <div>
                        <dt class="asp-detail-dt">Customer</dt>
                        <dd class="asp-detail-dd">
                            @if ($jobCard->customer && $routeAccess->allows($user, 'customers.show'))
                                <a href="{{ route('customers.show', $jobCard->customer) }}" class="asp-detail-link">
                                    {{ $jobCard->customer->full_name }}
                                    <span class="material-symbols-outlined text-sm">open_in_new</span>
                                </a>
                            @else
                                {{ $jobCard->customer?->full_name ?? 'N/A' }}
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="asp-detail-dt">Vehicle</dt>
                        <dd class="asp-detail-dd">
                            @if ($jobCard->vehicle && $routeAccess->allows($user, 'vehicles.show'))
                                <a href="{{ route('vehicles.show', $jobCard->vehicle) }}" class="asp-detail-link">
                                    {{ $jobCard->vehicle->registration_number }}
                                    <span class="material-symbols-outlined text-sm">open_in_new</span>
                                </a>
                            @else
                                {{ $jobCard->vehicle?->registration_number ?? 'N/A' }}
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="asp-detail-dt">Logged by</dt>
                        <dd class="asp-detail-dd">{{ $jobCard->creator?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="asp-detail-dt">Assigned washer</dt>
                        <dd class="asp-detail-dd">
                            @if ($jobCard->assignee && $routeAccess->allows($user, 'employees.show'))
                                <a href="{{ route('employees.show', $jobCard->assignee) }}" class="asp-detail-link">
                                    {{ $jobCard->assignee->displayName() }}
                                    <span class="material-symbols-outlined text-sm">open_in_new</span>
                                </a>
                            @else
                                {{ $jobCard->assignee?->displayName() ?? 'Unassigned' }}
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="asp-detail-dt">Booking</dt>
                        <dd class="asp-detail-dd">
                            @if ($jobCard->booking && $routeAccess->allows($user, 'bookings.show'))
                                <a href="{{ route('bookings.show', $jobCard->booking) }}" class="asp-detail-link">
                                    #{{ $jobCard->booking_id }}
                                    <span class="material-symbols-outlined text-sm">open_in_new</span>
                                </a>
                            @elseif ($jobCard->booking_id)
                                #{{ $jobCard->booking_id }}
                            @else
                                <span class="text-slate-400">Walk-in</span>
                            @endif
                        </dd>
                    </div>
                    @if ($canViewInvoice)
                        <div>
                            <dt class="asp-detail-dt">Invoice</dt>
                            <dd class="asp-detail-dd">
                                <a href="{{ route('invoices.show', $jobCard->invoice) }}" class="asp-detail-link">
                                    {{ $jobCard->invoice->invoice_number }}
                                    <span class="material-symbols-outlined text-sm">open_in_new</span>
                                </a>
                            </dd>
                        </div>
                    @endif
                    @if ($canViewCommission)
                        <div>
                            <dt class="asp-detail-dt">Commission</dt>
                            <dd class="asp-detail-dd">
                                <a href="{{ route('commissions.show', $commission) }}" class="asp-detail-link">
                                    KES {{ number_format($commission->amount, 0) }}
                                    <span class="material-symbols-outlined text-sm">open_in_new</span>
                                </a>
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>

        {{-- Services --}}
        <div class="asp-panel lg:col-span-3">
            <div class="asp-panel-header">
                <div>
                    <h2 class="asp-panel-title">Services</h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                        {{ $jobCard->services->count() }}
                        {{ Str::plural('service', $jobCard->services->count()) }}
                        @if ($servicesTotal > 0)
                            · KES {{ number_format($servicesTotal, 0) }}
                        @endif
                    </p>
                </div>
                <span class="material-symbols-outlined text-brand-primary-dim dark:text-brand-primary">local_car_wash</span>
            </div>
            <div class="asp-panel-body">
                @forelse ($jobCard->services as $line)
                    <div @class(['asp-service-row', 'mb-2' => ! $loop->last])>
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="asp-service-row-icon">
                                <span class="material-symbols-outlined text-lg">local_car_wash</span>
                            </span>
                            <div class="min-w-0">
                                <p class="truncate font-medium text-slate-900 dark:text-white">
                                    {{ $line->service?->name ?? 'Service' }}
                                </p>
                                @if ($line->status)
                                    <p class="text-xs capitalize text-slate-500 dark:text-slate-400">{{ $line->status }}</p>
                                @endif
                            </div>
                        </div>
                        <p class="shrink-0 font-mono text-sm font-semibold text-slate-900 dark:text-white">
                            KES {{ number_format($line->price, 0) }}
                        </p>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center py-10 text-center">
                        <span class="material-symbols-outlined mb-2 text-4xl text-slate-300 dark:text-slate-600">design_services</span>
                        <p class="text-sm font-medium text-slate-600 dark:text-slate-300">No services on this job card</p>
                        @if ($canEdit)
                            <a href="{{ route('job-cards.edit', $jobCard) }}" class="asp-detail-link mt-3 text-sm">
                                <span class="material-symbols-outlined text-base">edit</span>
                                Add services
                            </a>
                        @endif
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    @if ($jobCard->products->isNotEmpty())
        <div class="asp-panel mt-6">
            <div class="asp-panel-header">
                <div>
                    <h2 class="asp-panel-title">Products Used</h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                        KES {{ number_format($productsTotal, 0) }} total
                    </p>
                </div>
                <span class="material-symbols-outlined text-brand-primary-dim dark:text-brand-primary">inventory_2</span>
            </div>
            <div class="asp-panel-body">
                @foreach ($jobCard->products as $line)
                    @php
                        $lineTotal = (float) $line->quantity * (float) $line->unit_price;
                    @endphp
                    <div @class(['asp-service-row', 'mb-2' => ! $loop->last])>
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="asp-service-row-icon">
                                <span class="material-symbols-outlined text-lg">inventory_2</span>
                            </span>
                            <div class="min-w-0">
                                <p class="truncate font-medium text-slate-900 dark:text-white">
                                    {{ $line->product_name ?? $line->product?->name ?? 'Product' }}
                                </p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    {{ rtrim(rtrim(number_format($line->quantity, 2), '0'), '.') }} × KES {{ number_format($line->unit_price, 0) }}
                                </p>
                            </div>
                        </div>
                        <p class="shrink-0 font-mono text-sm font-semibold text-slate-900 dark:text-white">
                            KES {{ number_format($lineTotal, 0) }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if ($checklistTotal > 0)
        <div class="asp-panel mt-6">
            <div class="asp-panel-header">
                <div>
                    <h2 class="asp-panel-title">Quality Checklist</h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                        {{ $checklistDone }} of {{ $checklistTotal }} complete
                    </p>
                </div>
                <span class="material-symbols-outlined text-brand-primary-dim dark:text-brand-primary">checklist</span>
            </div>
            <div class="asp-panel-body space-y-2">
                @foreach ($jobCard->checklistItems as $item)
                    <div class="flex items-center gap-3 rounded-xl border border-slate-200/80 px-4 py-3 dark:border-brand-border/60">
                        <span @class([
                            'flex h-8 w-8 shrink-0 items-center justify-center rounded-lg',
                            'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400' => $item->is_completed,
                            'bg-slate-100 text-slate-400 dark:bg-brand-surface/60' => ! $item->is_completed,
                        ])>
                            <span class="material-symbols-outlined text-lg">{{ $item->is_completed ? 'check' : 'radio_button_unchecked' }}</span>
                        </span>
                        <p @class([
                            'text-sm font-medium',
                            'text-slate-900 dark:text-white' => $item->is_completed,
                            'text-slate-500 dark:text-slate-400' => ! $item->is_completed,
                        ])>{{ $item->label }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if ($jobCard->notes)
        <div class="asp-panel mt-6">
            <div class="asp-panel-header">
                <h2 class="asp-panel-title">Notes</h2>
                <span class="material-symbols-outlined text-brand-primary-dim dark:text-brand-primary">sticky_note_2</span>
            </div>
            <div class="asp-panel-body">
                <p class="text-sm leading-relaxed text-slate-600 dark:text-slate-300">{{ $jobCard->notes }}</p>
            </div>
        </div>
    @endif

    <footer class="asp-meta-line mt-6">
        <span>Job card #{{ $jobCard->id }}</span>
        <span>Logged by {{ $jobCard->creator?->name ?? 'Unknown' }}</span>
        <span>Created {{ $jobCard->created_at?->format('M j, Y g:i A') }}</span>
        @if ($jobCard->started_at)
            <span>Wash started {{ $jobCard->started_at->format('g:i A') }}</span>
        @endif
        @if ($jobCard->completed_at)
            <span>Ready at {{ $jobCard->completed_at->format('g:i A') }}</span>
        @endif
        <span>Ref {{ Str::upper(Str::limit($jobCard->uuid, 8, '')) }}</span>
    </footer>
</x-layouts.app>
