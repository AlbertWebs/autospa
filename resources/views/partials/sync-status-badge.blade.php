{{-- Mission Control / header sync status. Sync required is an active button. --}}
<span
    x-show="$store.offline.online && $store.offline.pending === 0 && ! $store.offline.syncing"
    x-cloak
    class="inline-flex items-center gap-1.5 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1.5 text-xs font-semibold text-emerald-600 dark:text-emerald-400"
    title="All local changes are synced with the server"
>
    <span class="material-symbols-outlined text-[14px]">cloud_done</span>
    Up to date
</span>

<span
    x-show="! $store.offline.online && $store.offline.pending === 0 && ! $store.offline.syncing"
    x-cloak
    class="inline-flex items-center gap-1.5 rounded-full border border-slate-400/30 bg-slate-500/10 px-3 py-1.5 text-xs font-semibold text-slate-600 dark:text-slate-400"
    title="You are offline — no pending changes to sync"
>
    <span class="material-symbols-outlined text-[14px]">cloud_off</span>
    Up to date
</span>

<button
    type="button"
    x-show="$store.offline.pending > 0 || $store.offline.syncing"
    x-cloak
    class="inline-flex items-center gap-1.5 rounded-full border border-amber-500/40 bg-amber-500/15 px-3 py-1.5 text-xs font-semibold text-amber-800 shadow-sm transition hover:bg-amber-500/25 disabled:cursor-not-allowed disabled:opacity-60 dark:border-amber-500/40 dark:bg-amber-500/15 dark:text-amber-300 dark:hover:bg-amber-500/25"
    x-bind:disabled="! $store.offline.online || $store.offline.syncing"
    x-bind:title="$store.offline.syncing
        ? 'Syncing local changes…'
        : ($store.offline.online
            ? 'Click to sync all offline changes now'
            : 'Reconnect to sync offline changes')"
    @click="$store.offline.syncNow()"
>
    <span class="material-symbols-outlined text-[14px]" x-show="! $store.offline.syncing">sync_problem</span>
    <span class="material-symbols-outlined animate-spin text-[14px]" x-show="$store.offline.syncing" x-cloak>sync</span>
    <span x-show="$store.offline.syncing" x-cloak>Syncing…</span>
    <span x-show="! $store.offline.syncing">
        Sync required
        <span x-show="$store.offline.pending > 0">(<span x-text="$store.offline.pending"></span>)</span>
        · Sync now
    </span>
</button>
