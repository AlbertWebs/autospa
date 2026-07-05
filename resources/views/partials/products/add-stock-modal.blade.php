<div
    x-show="showModal"
    x-cloak
    class="asp-modal-backdrop"
    @keydown.escape.window="showModal && closeModal()"
>
    <div
        class="asp-modal"
        @click.outside="closeModal()"
        x-transition
    >
        <div class="asp-modal-header">
            <div>
                <p class="font-mono text-[10px] font-semibold uppercase tracking-widest text-brand-primary">Inventory</p>
                <h3 class="asp-modal-title">Add Stock</h3>
            </div>
            <button type="button" class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-brand-surface-high dark:hover:text-slate-200" @click="closeModal()">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form @submit.prevent="submitStock" class="asp-form !space-y-5">
            <div class="asp-modal-body space-y-5">
                <div
                    class="asp-form-alert mb-0"
                    x-show="Object.keys(errors).filter(key => errors[key]?.length).length > 0"
                    x-cloak
                    style="display: none;"
                >
                    <span class="material-symbols-outlined shrink-0 text-lg">error</span>
                    <p>Please fix the errors below.</p>
                </div>

                <x-ui.form-field label="Product" for="add_stock_product_id" required>
                    <select
                        id="add_stock_product_id"
                        x-model="form.product_id"
                        class="asp-select"
                        x-bind:class="{ 'asp-select--error': errors.product_id }"
                        required
                    >
                        <option value="">Select product…</option>
                        <template x-for="product in products" :key="product.id">
                            <option
                                :value="product.id"
                                x-text="`${product.name} (${product.sku}) — ${product.quantity_on_hand} ${product.unit} on hand`"
                            ></option>
                        </template>
                    </select>
                    <p class="asp-field-error" x-show="errors.product_id" x-cloak>
                        <span class="material-symbols-outlined text-sm">error</span>
                        <span x-text="errors.product_id?.[0]"></span>
                    </p>
                </x-ui.form-field>

                <div class="rounded-xl border border-slate-200/80 bg-slate-50 px-4 py-3 text-sm dark:border-brand-border/60 dark:bg-brand-surface-high" x-show="selectedProduct" x-cloak>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-slate-500 dark:text-slate-400">Current stock</span>
                        <span class="font-mono font-medium text-slate-900 dark:text-white" x-text="selectedProduct ? `${selectedProduct.quantity_on_hand} ${selectedProduct.unit}` : ''"></span>
                    </div>
                </div>

                <div class="asp-form-grid">
                    <x-ui.form-field label="Quantity to Add" for="add_stock_quantity" required>
                        <x-ui.input
                            id="add_stock_quantity"
                            type="number"
                            step="0.01"
                            min="0.01"
                            x-model="form.quantity"
                            x-bind:class="{ 'asp-input--error': errors.quantity }"
                            required
                        />
                        <p class="asp-field-error" x-show="errors.quantity" x-cloak>
                            <span class="material-symbols-outlined text-sm">error</span>
                            <span x-text="errors.quantity?.[0]"></span>
                        </p>
                    </x-ui.form-field>

                    <x-ui.form-field label="Date & Time" for="add_stock_moved_at" required>
                        <x-ui.input
                            id="add_stock_moved_at"
                            type="datetime-local"
                            x-model="form.moved_at"
                            x-bind:class="{ 'asp-input--error': errors.moved_at }"
                            required
                        />
                        <p class="asp-field-error" x-show="errors.moved_at" x-cloak>
                            <span class="material-symbols-outlined text-sm">error</span>
                            <span x-text="errors.moved_at?.[0]"></span>
                        </p>
                    </x-ui.form-field>
                </div>

                <x-ui.form-field label="Notes" for="add_stock_notes">
                    <x-ui.textarea
                        id="add_stock_notes"
                        rows="3"
                        x-model="form.notes"
                        x-bind:class="{ 'asp-input--error': errors.notes }"
                        placeholder="Optional note for this stock receipt"
                    ></x-ui.textarea>
                    <p class="asp-field-error" x-show="errors.notes" x-cloak>
                        <span class="material-symbols-outlined text-sm">error</span>
                        <span x-text="errors.notes?.[0]"></span>
                    </p>
                </x-ui.form-field>
            </div>

            <div class="asp-modal-footer">
                <button type="button" class="asp-btn asp-btn-ghost" @click="closeModal()" x-bind:disabled="loading">
                    Cancel
                </button>
                <button type="submit" class="asp-btn asp-btn-primary min-w-[10rem]" x-bind:disabled="loading">
                    <svg x-show="loading" x-cloak class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span class="material-symbols-outlined text-lg" x-show="!loading">add_box</span>
                    <span x-text="loading ? 'Saving…' : 'Add Stock'"></span>
                </button>
            </div>
        </form>
    </div>
</div>
