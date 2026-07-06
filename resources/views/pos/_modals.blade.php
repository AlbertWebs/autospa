<div
    x-show="showCashModal"
    x-cloak
    class="asp-modal-backdrop"
    @keydown.escape.window="showCashModal && closeCashModal()"
>
    <div class="asp-modal" @click.outside="closeCashModal()" x-transition>
        <div class="asp-modal-header">
            <div>
                <p class="font-mono text-[10px] font-semibold uppercase tracking-widest text-emerald-600 dark:text-emerald-400">Cash Payment</p>
                <h3 class="asp-modal-title">Confirm Cash Received</h3>
            </div>
            <button type="button" class="rounded-lg p-1 text-slate-400" @click="closeCashModal()">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <div class="asp-modal-body space-y-5">
            <div class="rounded-xl border border-emerald-200/80 bg-emerald-50/80 px-4 py-4 dark:border-emerald-500/20 dark:bg-emerald-500/10">
                <p class="text-sm font-medium text-slate-900 dark:text-white">Have you received the cash payment?</p>
            </div>
            <div class="rounded-xl border border-slate-200/80 bg-slate-50 px-4 py-3 text-sm dark:border-brand-border/60 dark:bg-brand-surface-high">
                <div class="flex justify-between"><span class="text-slate-500">Amount</span><span class="font-mono font-semibold">KES <span x-text="formatMoney(total)"></span></span></div>
            </div>
        </div>
        <div class="asp-modal-footer">
            <button type="button" class="asp-btn asp-btn-ghost" @click="closeCashModal()">Not yet</button>
            <button type="button" class="asp-btn asp-btn-primary" @click="confirmCashReceived()" x-bind:disabled="checkoutSubmitting">Yes, cash received</button>
        </div>
    </div>
</div>

<div
    x-show="showStkModal"
    x-cloak
    class="asp-modal-backdrop"
    @keydown.escape.window="showStkModal && closeStkModal()"
>
    <div class="asp-modal" @click.outside="closeStkModal()" x-transition>
        <div class="asp-modal-header">
            <div>
                <p class="font-mono text-[10px] font-semibold uppercase tracking-widest text-brand-primary">M-Pesa</p>
                <h3 class="asp-modal-title">Send STK Push</h3>
            </div>
            <button type="button" class="rounded-lg p-1 text-slate-400" @click="closeStkModal()">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form @submit.prevent="confirmStkPush" class="asp-form !space-y-5">
            <div class="asp-modal-body">
                <x-ui.form-field label="Phone Number" for="stk_phone">
                    <x-ui.input id="stk_phone" type="tel" x-model="stkPhoneDraft" placeholder="+2547XXXXXXXX" required />
                </x-ui.form-field>
            </div>
            <div class="asp-modal-footer">
                <button type="button" class="asp-btn asp-btn-ghost" @click="closeStkModal()">Cancel</button>
                <button type="submit" class="asp-btn asp-btn-primary" x-bind:disabled="stkPushLoading">Send STK Push</button>
            </div>
        </form>
    </div>
</div>
