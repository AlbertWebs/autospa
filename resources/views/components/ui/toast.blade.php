<div class="fixed bottom-4 right-4 z-[60] flex flex-col gap-2" x-data>
    <template x-for="item in $store.toast.items" :key="item.id">
        <div x-show="true" x-transition
            :class="{
                'bg-emerald-600': item.type === 'success',
                'bg-red-600': item.type === 'error',
                'bg-slate-800': item.type === 'info'
            }"
            class="rounded-xl px-4 py-3 text-sm font-medium text-white shadow-lg">
            <span x-text="item.message"></span>
        </div>
    </template>
</div>
