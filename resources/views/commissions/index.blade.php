<div
    x-data="commissionMpesaPayout({
        initiateUrl: @js(route('commissions.pay.mpesa.initiate')),
        confirmUrl: @js(route('commissions.pay.mpesa.confirm')),
        csrfToken: @js(csrf_token()),
    })"
>
<x-ui.index-page
    eyebrow="Daily Commissions"
    title="Daily Commissions"
    subtitle="Washers earn commission after each wash. Payouts are settled daily."
>
    <x-slot name="actions">
        <form method="GET" action="{{ route('commissions.index') }}" class="flex items-center gap-2">
            <input
                type="date"
                name="date"
                value="{{ $date->toDateString() }}"
                class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-brand-border dark:bg-brand-surface"
            >
            <button type="submit" class="rounded-xl bg-brand-primary px-4 py-2 text-sm font-semibold text-brand-on-primary">
                View day
            </button>
        </form>
    </x-slot>

    @unless ($commissionsEnabled)
        <x-ui.card class="mb-6">
            <p class="text-sm text-slate-600 dark:text-slate-300">
                Commissions are disabled. Enable them in
                <a href="{{ route('settings.company') }}" class="font-semibold text-brand-primary">Company Settings</a>
                to start tracking washer earnings automatically.
            </p>
        </x-ui.card>
    @endunless

    @if ($commissionsEnabled && $totals['washers'] > 0 && $totals['earned'] <= 0)
        <x-ui.card class="mb-6">
            <p class="text-sm text-slate-600 dark:text-slate-300">
                Completed washes are recorded, but no commission was accrued. Assign an <strong>attendee</strong> (not a supervisor) on the job card before completing the wash.
            </p>
        </x-ui.card>
    @endif

    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <x-ui.stat-card variant="payments" label="Earned" icon="payments"
            :value="'KES ' . number_format($totals['earned'], 0)" :hint="$date->isToday() ? 'All washer commissions today' : 'All washer commissions on this day'" />
        <x-ui.stat-card variant="revenue" label="Pending Payout" icon="schedule"
            :value="'KES ' . number_format($totals['pending'], 0)" hint="Awaiting daily settlement" />
        <x-ui.stat-card variant="service" label="Washers" icon="groups"
            :value="$totals['washers']" :hint="$date->isToday() ? 'Completed washes today' : 'Completed washes on this day'" />
    </div>

    <x-ui.panel title="Washer payouts · {{ $date->format('l, M j, Y') }}" class="mb-8">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 text-left text-xs uppercase tracking-wide text-slate-500 dark:border-brand-border">
                        <th class="px-3 py-3">Washer</th>
                        <th class="px-3 py-3">Washes</th>
                        <th class="px-3 py-3">Earned</th>
                        <th class="px-3 py-3">Pending</th>
                        <th class="px-3 py-3">Paid</th>
                        <th class="px-3 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dailySummary as $row)
                        <tr class="border-b border-slate-100 dark:border-brand-border/60">
                            <td class="px-3 py-4">
                                <p class="font-semibold text-slate-900 dark:text-white">{{ $row['employee']->full_name }}</p>
                                <p class="text-xs text-slate-500">{{ $row['employee']->typeLabel() }}</p>
                            </td>
                            <td class="px-3 py-4">{{ $row['washes'] }}</td>
                            <td class="px-3 py-4">KES {{ number_format($row['earned'], 0) }}</td>
                            <td class="px-3 py-4">KES {{ number_format($row['pending'], 0) }}</td>
                            <td class="px-3 py-4">
                                @if ($row['paid'] > 0)
                                    <x-ui.badge color="emerald">KES {{ number_format($row['paid'], 0) }}</x-ui.badge>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-3 py-4">
                                @if ($row['pending'] > 0)
                                    @can('staff.manage')
                                        <div class="flex flex-wrap justify-end gap-2">
                                            <form method="POST" action="{{ route('commissions.pay') }}">
                                                @csrf
                                                <input type="hidden" name="employee_id" value="{{ $row['employee']->id }}">
                                                <input type="hidden" name="date" value="{{ $date->toDateString() }}">
                                                <button type="submit" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-brand-border dark:text-slate-200">
                                                    Mark paid
                                                </button>
                                            </form>
                                            @if ($row['employee']->phone)
                                                <button
                                                    type="button"
                                                    class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-500"
                                                    @click="startMpesaPayout({{ $row['employee']->id }}, @js($date->toDateString()), @js($row['employee']->full_name), {{ $row['pending'] }})"
                                                >
                                                    Send M-Pesa
                                                </button>
                                            @endif
                                        </div>
                                    @else
                                        <span class="float-right text-xs text-amber-600">Pending payout</span>
                                    @endcan
                                @else
                                    <span class="float-right text-xs text-slate-400">Settled</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-8">
                                <x-ui.empty-state
                                    title="No washer activity"
                                    description="Assign staff to job cards and complete washes to accrue commissions."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.panel>

    <x-ui.data-table
        :paginator="$recentCommissions"
        :empty="$recentCommissions->isEmpty()"
        empty-title="No commission lines"
        empty-description="Individual commission entries for this day will appear here."
    >
        <x-slot name="header">
            <x-ui.th>Washer</x-ui.th>
            <x-ui.th>Wash</x-ui.th>
            <x-ui.th>Amount</x-ui.th>
            <x-ui.th>Rate</x-ui.th>
            <x-ui.th>Status</x-ui.th>
            <x-ui.th></x-ui.th>
        </x-slot>

        @foreach ($recentCommissions as $commission)
            <tr class="asp-table-row">
                <x-ui.td primary>{{ $commission->employee?->full_name ?? 'N/A' }}</x-ui.td>
                <x-ui.td muted>
                    @if ($commission->reference)
                        Job #{{ $commission->reference_id }}
                    @else
                        —
                    @endif
                </x-ui.td>
                <x-ui.td>KES {{ number_format($commission->amount ?? 0, 0) }}</x-ui.td>
                <x-ui.td muted>{{ $commission->rate ? number_format($commission->rate * 100, 0).'%' : '—' }}</x-ui.td>
                <x-ui.td>
                    <x-ui.badge :color="$commission->status === 'paid' ? 'emerald' : 'indigo'">
                        {{ ucfirst($commission->status ?? 'pending') }}
                    </x-ui.badge>
                </x-ui.td>
                <x-ui.td>
                    <a href="{{ route('commissions.show', $commission) }}" class="text-xs font-semibold text-brand-primary">Details</a>
                </x-ui.td>
            </tr>
        @endforeach
    </x-ui.data-table>
</x-ui.index-page>

    @include('commissions._mpesa-otp-modal')
</div>
