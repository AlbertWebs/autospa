<div x-data="{ show: false }" x-on:open-modal.window="if ($event.detail === '{{ $name }}') show = true" x-on:close-modal.window="if ($event.detail === '{{ $name }}') show = false" x-on:keydown.escape.window="show = false">
    <div x-show="show" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="flex min-h-screen items-center justify-center p-4">
            <div x-show="show" x-transition.opacity class="fixed inset-0 bg-black/50" @click="show = false"></div>
            <div x-show="show" x-transition class="relative w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-6 shadow-xl dark:border-slate-700 dark:bg-slate-900">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
