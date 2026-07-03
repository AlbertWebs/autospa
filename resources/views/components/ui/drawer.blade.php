<div x-data="{ open: false }" @open-drawer.window="if ($event.detail === '{{ $name }}') open = true" @close-drawer.window="if ($event.detail === '{{ $name }}') open = false">
    <div x-show="open" class="fixed inset-0 z-50" x-cloak>
        <div x-show="open" x-transition.opacity class="fixed inset-0 bg-black/50" @click="open = false"></div>
        <div x-show="open" x-transition:enter="transform transition ease-in-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in-out duration-300" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
            class="fixed inset-y-0 right-0 w-full max-w-md border-l border-slate-200 bg-white shadow-xl dark:border-slate-800 dark:bg-slate-900">
            <div class="flex h-full flex-col">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
