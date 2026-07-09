<div
    x-show="! $store.offline.online || $store.offline.pending > 0"
    x-cloak
    class="border-b border-amber-200/80 bg-amber-50 px-4 py-2 text-sm text-amber-950 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-100"
>
    <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-2">
        <div class="flex flex-wrap items-center gap-x-4 gap-y-1">
            <p x-show="! $store.offline.online" class="font-medium">
                Offline — only tools that queue changes for sync are shown
            </p>
            <p x-show="$store.offline.pending > 0" class="text-amber-800 dark:text-amber-200">
                <span x-text="$store.offline.pending"></span>
                <span x-text="$store.offline.pending === 1 ? ' change pending sync' : ' changes pending sync'"></span>
            </p>
        </div>

        <button
            type="button"
            x-show="$store.offline.online && $store.offline.pending > 0"
            x-cloak
            class="rounded-lg border border-amber-300/80 bg-white px-3 py-1 text-xs font-semibold text-amber-900 hover:bg-amber-100 disabled:opacity-60 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-100 dark:hover:bg-amber-500/20"
            x-bind:disabled="$store.offline.syncing"
            @click="$store.offline.syncNow()"
        >
            <span x-show="! $store.offline.syncing">Sync now</span>
            <span x-show="$store.offline.syncing" x-cloak>Syncing…</span>
        </button>
    </div>
</div>
