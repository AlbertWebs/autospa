<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Attendance</h1></x-slot>
    @include('partials.crud.index-header', ['createRoute' => route('attendance.create'), 'createLabel' => 'Record Attendance', 'title' => 'Attendance'])
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Employee</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Clock In/Out</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-500">Actions</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                    @forelse ($attendance as $record)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="whitespace-nowrap px-6 py-4 font-medium">{{ $record->employee?->full_name ?? '—' }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ $record->date?->format('M j, Y') }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-slate-500">{{ $record->clock_in ?? '—' }} / {{ $record->clock_out ?? '—' }}</td>
                            <td class="whitespace-nowrap px-6 py-4"><x-ui.badge color="indigo">{{ ucfirst(str_replace('_', ' ', $record->status)) }}</x-ui.badge></td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <a href="{{ route('attendance.show', $record) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">View</a>
                            </td>
                        </tr>
                    @empty @endforelse
                </tbody>
            </table>
        </div>
        @if ($attendance->isEmpty())<x-ui.empty-state title="No attendance records" description="Record employee attendance here." />@endif
        @include('partials.crud.pagination', ['paginator' => $attendance])
    </x-ui.card>
</x-layouts.app>
