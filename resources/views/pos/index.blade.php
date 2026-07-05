@php
    $customersJson = $customers->map(function ($customer) {
        $customerName = filled(trim((string) $customer->full_name)) ? trim((string) $customer->full_name) : null;
        $primaryVehicle = $customer->vehicles->first()?->registration_number;
        $additionalVehicleCount = max($customer->vehicles->count() - 1, 0);
        $vehicleSummary = $primaryVehicle
            ? $primaryVehicle . ($additionalVehicleCount > 0 ? ' +' . $additionalVehicleCount . ' more' : '')
            : null;

        return [
            'id' => $customer->id,
            'display_name' => $customerName,
            'phone' => $customer->phone,
            'vehicle_summary' => $vehicleSummary,
            'option_label' => $customerName
                ? $customerName . ($vehicleSummary ? ' · ' . $vehicleSummary : '')
                : ($vehicleSummary ?? 'Unnamed customer'),
        ];
    })->values();

    $servicesJson = $services->map(fn ($s) => [
        'id' => $s->id,
        'name' => $s->name,
        'price' => (float) $s->price,
        'category' => $s->category?->name,
    ])->values();

    $productsJson = $products->map(fn ($p) => [
        'id' => $p->id,
        'name' => $p->name,
        'price' => (float) $p->selling_price,
        'sku' => $p->sku,
    ])->values();

    $paymentMethodsJson = $paymentMethods->map(fn ($m) => [
        'id' => $m->id,
        'name' => $m->name,
        'slug' => $m->slug,
    ])->values();
@endphp

<x-layouts.app>
    <x-slot name="header">
        <span class="hidden sm:inline">Point of Sale</span>
    </x-slot>

    <div
        x-data="posCheckout({
            customers: @js($customersJson),
            customerStoreUrl: @js(route('customers.store')),
            stkPushUrl: @js(route('pos.stk-push')),
            services: @js($servicesJson),
            products: @js($productsJson),
            paymentMethods: @js($paymentMethodsJson),
            defaultCustomerId: @js(old('customer_id', $customers->first()?->id ?? '')),
            oldCustomerId: @js(old('customer_id')),
            oldPaymentMethodId: @js(old('payment_method_id')),
            oldItems: @js(old('items', [])),
        })"
    >
        @if ($errors->any())
            <script>
                document.addEventListener('alpine:init', () => {
                    Alpine.store('toast').show(@json($errors->first()), 'error');
                });
            </script>
        @endif

        <header class="asp-page-header">
            <div>
                <p class="asp-page-eyebrow">Sales</p>
                <h1 class="asp-page-title">Point of Sale</h1>
                <p class="asp-page-subtitle">Add services and products to the cart, then complete checkout.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <div class="rounded-xl border border-slate-200/80 bg-white px-4 py-2.5 dark:border-brand-border/60 dark:bg-brand-surface-high">
                    <p class="font-mono text-[10px] font-semibold uppercase tracking-widest text-slate-400">Cart</p>
                    <p class="font-mono text-sm font-bold text-slate-900 dark:text-white">
                        <span x-text="itemCount"></span> items · KES <span x-text="formatMoney(total)"></span>
                    </p>
                </div>
            </div>
        </header>

        <div class="asp-pos-layout">
            {{-- Catalog --}}
            <div class="asp-pos-catalog">
                <div class="asp-panel overflow-hidden">
                    <div class="asp-panel-header">
                        <div>
                            <h2 class="asp-panel-title">Catalog</h2>
                            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Tap an item to add it to the cart.</p>
                        </div>
                        <span class="material-symbols-outlined text-brand-primary-dim dark:text-brand-primary">storefront</span>
                    </div>

                    <div class="border-b border-slate-200/80 px-5 py-4 dark:border-brand-border/60">
                        <div class="asp-pos-toolbar">
                            <div class="asp-pos-tabs">
                                <button type="button" class="asp-pos-tab" x-bind:class="{ 'asp-pos-tab--active': catalogTab === 'all' }" @click="catalogTab = 'all'">All</button>
                                <button type="button" class="asp-pos-tab" x-bind:class="{ 'asp-pos-tab--active': catalogTab === 'service' }" @click="catalogTab = 'service'">Services</button>
                                <button type="button" class="asp-pos-tab" x-bind:class="{ 'asp-pos-tab--active': catalogTab === 'product' }" @click="catalogTab = 'product'">Products</button>
                            </div>
                            <div class="asp-pos-search-wrap">
                                <span class="material-symbols-outlined asp-pos-search-icon">search</span>
                                <input type="search" x-model="search" placeholder="Search catalog…" class="asp-pos-search" />
                            </div>
                        </div>
                    </div>

                    <div class="asp-pos-grid">
                        <template x-for="item in filteredItems" :key="item.itemType + '-' + item.id">
                            <button type="button" class="group asp-pos-tile" @click="addItem(item)">
                                <span class="asp-pos-tile-add material-symbols-outlined text-lg">add</span>
                                <span class="asp-pos-tile-type" x-bind:class="item.itemType === 'service' ? 'asp-pos-tile-type--service' : 'asp-pos-tile-type--product'" x-text="item.itemType"></span>
                                <span class="asp-pos-tile-name" x-text="item.name"></span>
                                <span class="asp-pos-tile-meta" x-show="item.category" x-text="item.category" x-cloak></span>
                                <span class="asp-pos-tile-meta" x-show="item.sku" x-text="item.sku" x-cloak></span>
                                <span class="asp-pos-tile-price">KES <span x-text="formatMoney(item.price)"></span></span>
                            </button>
                        </template>
                    </div>

                    <div x-show="filteredItems.length === 0" x-cloak class="p-6">
                        <div class="asp-pos-empty">
                            <span class="material-symbols-outlined mb-2 text-3xl text-slate-300">inventory_2</span>
                            <p class="text-sm font-medium text-slate-600 dark:text-slate-300">No items found</p>
                            <p class="mt-1 text-xs text-slate-500">Try a different search or tab.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Cart --}}
            <div class="lg:col-span-1">
                <div class="asp-pos-cart">
                    <div class="asp-pos-cart-header">
                        <div>
                            <h2 class="asp-panel-title">Checkout</h2>
                            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                <span x-text="itemCount"></span> item(s) in cart
                            </p>
                        </div>
                        <button
                            type="button"
                            class="asp-btn asp-btn-ghost !px-2 !py-1.5 text-xs"
                            x-show="cart.length > 0"
                            x-cloak
                            @click="clearCart()"
                        >
                            Clear
                        </button>
                    </div>

                    <form method="POST" action="{{ route('pos.store') }}" class="asp-pos-cart-body asp-form !space-y-4" @submit="handleCheckout">
                        @csrf

                        <input type="hidden" name="customer_id" x-model="customerId">
                        <input type="hidden" name="payment_method_id" x-bind:value="paymentMethodId">
                        <input type="hidden" name="method" x-bind:value="selectedMethodSlug">
                        <input type="hidden" name="stk_phone" x-model="stkPhone">
                        <input type="hidden" name="stk_reference" x-model="stkReference">
                        <input type="hidden" name="stk_status" x-model="stkStatus">
                        <input type="hidden" name="subtotal" x-bind:value="subtotal.toFixed(2)">
                        <input type="hidden" name="discount_amount" value="0">
                        <input type="hidden" name="tax_amount" value="0">
                        <input type="hidden" name="total_amount" x-bind:value="total.toFixed(2)">

                        <div
                            x-show="showCheckoutGuide"
                            x-cloak
                            class="rounded-2xl border border-slate-200/80 bg-slate-50 px-4 py-3 dark:border-brand-border/60 dark:bg-brand-surface-high/60"
                        >
                            <p class="font-mono text-[10px] font-semibold uppercase tracking-widest text-slate-400">Checkout Steps</p>
                            <ol class="mt-2 space-y-1 text-sm text-slate-600 dark:text-slate-300">
                                <li>1. Select or create a customer.</li>
                                <li>2. Add services or products to the cart.</li>
                                <li>3. Choose a payment method.</li>
                                <li>4. Complete the sale to issue a receipt.</li>
                            </ol>
                            <div class="mt-3 flex justify-end">
                                <button
                                    type="button"
                                    class="asp-btn asp-btn-primary !px-4 !py-2 text-sm"
                                    @click="dismissCheckoutGuide()"
                                >
                                    Understood
                                </button>
                            </div>
                        </div>

                        <x-ui.form-field label="Customer" for="pos_customer" hint="Required for invoicing.">
                            <div class="asp-field-addon">
                                <x-ui.select id="pos_customer" x-model="customerId" required>
                                    <option value="">Select customer…</option>
                                    <template x-for="customer in customers" :key="customer.id">
                                        <option :value="customer.id" x-text="customer.option_label"></option>
                                    </template>
                                </x-ui.select>
                                <button
                                    type="button"
                                    class="asp-btn asp-btn-secondary shrink-0 !px-3"
                                    title="Create new customer"
                                    @click="openCustomerModal()"
                                >
                                    <span class="material-symbols-outlined text-lg">person_add</span>
                                </button>
                            </div>
                            <p class="asp-field-hint">
                                <button type="button" class="text-brand-primary-dim hover:underline dark:text-brand-primary" @click="openCustomerModal()">
                                    + Add new customer
                                </button>
                            </p>
                            <p
                                class="mt-2 text-xs text-slate-500 dark:text-slate-400"
                                x-show="selectedCustomerVehicle"
                                x-cloak
                            >
                                Vehicle: <span class="font-mono" x-text="selectedCustomerVehicle"></span>
                            </p>
                        </x-ui.form-field>

                        <div class="asp-pos-cart-lines">
                            <template x-if="cart.length === 0">
                                <div class="asp-pos-empty !py-8">
                                    <span class="material-symbols-outlined mb-2 text-3xl text-slate-300">shopping_cart</span>
                                    <p class="text-sm text-slate-500">Cart is empty</p>
                                    <p class="mt-1 text-xs text-slate-400">Select items from the catalog.</p>
                                </div>
                            </template>

                            <template x-for="(item, index) in cart" :key="index">
                                <div class="asp-pos-line">
                                    <div class="asp-pos-line-info">
                                        <p class="asp-pos-line-name" x-text="item.name"></p>
                                        <p class="asp-pos-line-meta">
                                            KES <span x-text="formatMoney(item.price)"></span> each
                                        </p>
                                    </div>
                                    <div class="flex flex-col items-end gap-2">
                                        <div class="asp-pos-qty">
                                            <button type="button" class="asp-pos-qty-btn rounded-l-lg" @click="decrementItem(index)">
                                                <span class="material-symbols-outlined text-base">remove</span>
                                            </button>
                                            <span class="asp-pos-qty-value" x-text="item.qty"></span>
                                            <button type="button" class="asp-pos-qty-btn rounded-r-lg" @click="incrementItem(index)">
                                                <span class="material-symbols-outlined text-base">add</span>
                                            </button>
                                        </div>
                                        <button type="button" class="text-xs text-rose-500 hover:text-rose-600" @click="removeItem(index)">Remove</button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="asp-pos-totals">
                            <div class="asp-pos-total-row">
                                <span class="text-slate-500">Subtotal</span>
                                <span class="font-mono">KES <span x-text="formatMoney(subtotal)"></span></span>
                            </div>
                            <div class="asp-pos-total-row asp-pos-total-row--grand">
                                <span>Total</span>
                                <span class="font-mono text-brand-primary-dim dark:text-brand-primary">KES <span x-text="formatMoney(total)"></span></span>
                            </div>
                        </div>

                        <x-ui.form-field label="Payment Method" for="payment_method">
                            <x-ui.select id="payment_method" x-model="paymentMethodId" required>
                                <option value="">Select method…</option>
                                @foreach ($paymentMethods as $method)
                                    <option value="{{ $method->id }}">{{ $method->name }}</option>
                                @endforeach
                            </x-ui.select>
                            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400" x-show="isMpesaSelected" x-cloak>
                                STK push will be sent to <span class="font-mono" x-text="selectedCustomerPhone || 'the customer phone'"></span>.
                            </p>
                        </x-ui.form-field>

                        <button
                            type="submit"
                            class="asp-btn asp-btn-primary w-full justify-center !py-3"
                            x-bind:disabled="!canCheckout || checkoutSubmitting"
                        >
                            <span class="material-symbols-outlined text-lg">payments</span>
                            <span x-text="isMpesaSelected ? 'Send STK Push & Issue Receipt' : 'Complete Sale & Issue Receipt'"></span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        @include('partials.customers.quick-create-modal')

        <div
            x-show="showCashModal"
            x-cloak
            class="asp-modal-backdrop"
            @keydown.escape.window="showCashModal && closeCashModal()"
        >
            <div
                class="asp-modal"
                @click.outside="closeCashModal()"
                x-transition
            >
                <div class="asp-modal-header">
                    <div>
                        <p class="font-mono text-[10px] font-semibold uppercase tracking-widest text-emerald-600 dark:text-emerald-400">Cash Payment</p>
                        <h3 class="asp-modal-title">Confirm Cash Received</h3>
                    </div>
                    <button type="button" class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-brand-surface-high dark:hover:text-slate-200" @click="closeCashModal()">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div class="asp-modal-body space-y-5">
                    <div class="flex items-start gap-4 rounded-2xl border border-emerald-200/80 bg-emerald-50/80 px-4 py-4 dark:border-emerald-500/20 dark:bg-emerald-500/10">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">
                            <span class="material-symbols-outlined text-2xl">payments</span>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-slate-900 dark:text-white">
                                Have you received the cash payment from the customer?
                            </p>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                                Confirm only after the full amount has been collected at the counter.
                            </p>
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-200/80 bg-slate-50 px-4 py-3 text-sm dark:border-brand-border/60 dark:bg-brand-surface-high">
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-500 dark:text-slate-400">Amount</span>
                            <span class="font-mono text-lg font-semibold text-emerald-700 dark:text-emerald-300">KES <span x-text="formatMoney(total)"></span></span>
                        </div>
                        <div class="mt-2 flex items-center justify-between gap-4">
                            <span class="text-slate-500 dark:text-slate-400">Customer</span>
                            <span class="font-medium text-slate-900 dark:text-white" x-text="selectedCustomer?.display_name || selectedCustomer?.option_label || 'Selected customer'"></span>
                        </div>
                        <div class="mt-2 flex items-center justify-between gap-4" x-show="selectedCustomerVehicle" x-cloak>
                            <span class="text-slate-500 dark:text-slate-400">Vehicle</span>
                            <span class="font-mono text-slate-900 dark:text-white" x-text="selectedCustomerVehicle"></span>
                        </div>
                    </div>
                </div>

                <div class="asp-modal-footer">
                    <button type="button" class="asp-btn asp-btn-ghost" @click="closeCashModal()" x-bind:disabled="checkoutSubmitting">
                        Not yet
                    </button>
                    <button type="button" class="asp-btn asp-btn-primary min-w-[12rem]" @click="confirmCashReceived()" x-bind:disabled="checkoutSubmitting">
                        <svg x-show="checkoutSubmitting" x-cloak class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span class="material-symbols-outlined text-lg" x-show="!checkoutSubmitting">check_circle</span>
                        <span x-text="checkoutSubmitting ? 'Completing…' : 'Yes, cash received'"></span>
                    </button>
                </div>
            </div>
        </div>

        <div
            x-show="showStkModal"
            x-cloak
            class="asp-modal-backdrop"
            @keydown.escape.window="showStkModal && closeStkModal()"
        >
            <div
                class="asp-modal"
                @click.outside="closeStkModal()"
                x-transition
            >
                <div class="asp-modal-header">
                    <div>
                        <p class="font-mono text-[10px] font-semibold uppercase tracking-widest text-brand-primary">M-Pesa</p>
                        <h3 class="asp-modal-title">Send STK Push</h3>
                    </div>
                    <button type="button" class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-brand-surface-high dark:hover:text-slate-200" @click="closeStkModal()">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <form @submit.prevent="confirmStkPush" class="asp-form !space-y-5">
                    <div class="asp-modal-body space-y-5">
                        <div class="rounded-xl border border-slate-200/80 bg-slate-50 px-4 py-3 text-sm dark:border-brand-border/60 dark:bg-brand-surface-high">
                            <div class="flex items-center justify-between gap-4">
                                <span class="text-slate-500 dark:text-slate-400">Amount</span>
                                <span class="font-mono font-semibold text-slate-900 dark:text-white">KES <span x-text="formatMoney(total)"></span></span>
                            </div>
                            <div class="mt-2 flex items-center justify-between gap-4">
                                <span class="text-slate-500 dark:text-slate-400">Customer</span>
                                <span class="font-medium text-slate-900 dark:text-white" x-text="selectedCustomer?.display_name || 'Selected customer'"></span>
                            </div>
                        </div>

                        <x-ui.form-field label="Phone Number" for="stk_phone" hint="Edit before sending if needed.">
                            <x-ui.input
                                id="stk_phone"
                                type="tel"
                                x-model="stkPhoneDraft"
                                x-bind:class="{ 'asp-input--error': stkPushErrors.phone }"
                                placeholder="+2547XXXXXXXX"
                                required
                            />
                            <p class="asp-field-error" x-show="stkPushErrors.phone" x-cloak>
                                <span class="material-symbols-outlined text-sm">error</span>
                                <span x-text="stkPushErrors.phone?.[0]"></span>
                            </p>
                        </x-ui.form-field>
                    </div>

                    <div class="asp-modal-footer">
                        <button type="button" class="asp-btn asp-btn-ghost" @click="closeStkModal()" x-bind:disabled="stkPushLoading">
                            Cancel
                        </button>
                        <button type="submit" class="asp-btn asp-btn-primary min-w-[12rem]" x-bind:disabled="stkPushLoading">
                            <svg x-show="stkPushLoading" x-cloak class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span class="material-symbols-outlined text-lg" x-show="!stkPushLoading">smartphone</span>
                            <span x-text="stkPushLoading ? 'Sending…' : 'Send STK Push & Complete Sale'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
