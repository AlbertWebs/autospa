<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Notifications</h1></x-slot>
    <x-ui.card :padding="false">
        <div class="divide-y divide-slate-200 dark:divide-slate-800">
            @forelse ($notifications as $notification)
                <div class="flex gap-4 px-6 py-4 {{ $notification->read_at ? '' : 'bg-indigo-50/50 dark:bg-indigo-950/20' }}">
                    <div class="flex-1">
                        <p class="font-medium">{{ $notification->data['title'] ?? 'Notification' }}</p>
                        <p class="text-sm text-slate-500">{{ $notification->data['message'] ?? '' }}</p>
                        <p class="mt-1 text-xs text-slate-400">{{ $notification->created_at?->diffForHumans() }}</p>
                    </div>
                    @unless($notification->read_at)
                        <x-ui.badge color="indigo">New</x-ui.badge>
                    @endunless
                </div>
            @empty
                <x-ui.empty-state title="No notifications" description="You're all caught up!" />
            @endforelse
        </div>
        @include('partials.crud.pagination', ['paginator' => $notifications])
    </x-ui.card>
</x-layouts.app>
