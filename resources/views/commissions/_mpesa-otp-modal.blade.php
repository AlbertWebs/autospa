<div
    x-show="showOtpModal"
    x-cloak
    class="asp-modal-backdrop"
    @keydown.escape.window="showOtpModal && closeOtpModal()"
>
    <div class="asp-modal" @click.outside="closeOtpModal()" x-transition>
        <div class="asp-modal-header">
            <div>
                <p class="font-mono text-[10px] font-semibold uppercase tracking-widest text-emerald-600 dark:text-emerald-400">M-Pesa B2C</p>
                <h3 class="asp-modal-title">Authorize Commission Payout</h3>
            </div>
            <button type="button" class="rounded-lg p-1 text-slate-400" @click="closeOtpModal()">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form @submit.prevent="confirmOtp" class="asp-form !space-y-5">
            <div class="asp-modal-body space-y-4">
                <div class="rounded-xl border border-emerald-200/80 bg-emerald-50/80 px-4 py-4 text-sm dark:border-emerald-500/20 dark:bg-emerald-500/10">
                    <p class="font-medium text-slate-900 dark:text-white">
                        Pay <span class="font-mono" x-text="formatMoney(payoutAmount)"></span> to
                        <span x-text="payoutEmployeeName"></span> via M-Pesa.
                    </p>
                    <p class="mt-2 text-slate-600 dark:text-slate-300">
                        We sent a one-time password to
                        <span class="font-mono font-semibold" x-text="otpSentTo"></span>.
                        Enter it below to release the payout.
                    </p>
                </div>

                <div class="rounded-xl border border-amber-200/80 bg-amber-50/80 px-4 py-3 text-xs text-amber-900 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-100" x-show="debugOtp" x-cloak>
                    Local dev OTP: <span class="font-mono font-semibold" x-text="debugOtp"></span>
                </div>

                <x-ui.form-field label="Admin OTP" for="commission_payout_otp">
                    <x-ui.input
                        id="commission_payout_otp"
                        type="text"
                        inputmode="numeric"
                        pattern="[0-9]{6}"
                        maxlength="6"
                        x-model="otp"
                        placeholder="6-digit code"
                        class="text-center font-mono text-lg tracking-[0.35em]"
                        required
                        autocomplete="one-time-code"
                    />
                </x-ui.form-field>
            </div>

            <div class="asp-modal-footer">
                <button type="button" class="asp-btn asp-btn-ghost" @click="closeOtpModal()">Cancel</button>
                <button type="submit" class="asp-btn asp-btn-primary !bg-emerald-600 hover:!bg-emerald-500" x-bind:disabled="confirming">
                    <span x-show="!confirming">Confirm &amp; Send M-Pesa</span>
                    <span x-show="confirming" x-cloak>Sending…</span>
                </button>
            </div>
        </form>
    </div>
</div>
