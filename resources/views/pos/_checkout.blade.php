@php
    $compact = $compact ?? false;

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

    @unless ($compact)
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
    @else
        <div class="mb-3 flex items-center justify-between rounded-xl border border-slate-200/80 bg-white px-4 py-3 dark:border-brand-border/60 dark:bg-brand-surface">
            <div>
                <p class="text-sm font-bold text-slate-900 dark:text-white">Point of Sale</p>
                <p class="text-xs text-slate-500">Tap items to add to cart</p>
            </div>
            <p class="font-mono text-sm font-bold text-brand-primary">
                <span x-text="itemCount"></span> · KES <span x-text="formatMoney(total)"></span>
            </p>
        </div>
    @endunless

    <div @class(['asp-pos-layout', 'asp-pos-layout--mobile' => $compact])>
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
                            <button type="button" class="asp-btn asp-btn-primary !px-4 !py-2 text-sm" @click="dismissCheckoutGuide()">
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
                            <button type="button" class="asp-btn asp-btn-secondary shrink-0 !px-3" title="Create new customer" @click="openCustomerModal()">
                                <span class="material-symbols-outlined text-lg">person_add</span>
                            </button>
                        </div>
                    </x-ui.form-field>

                    <div class="asp-pos-cart-lines">
                        <template x-if="cart.length === 0">
                            <div class="asp-pos-empty !py-8">
                                <span class="material-symbols-outlined mb-2 text-3xl text-slate-300">shopping_cart</span>
                                <p class="text-sm text-slate-500">Cart is empty</p>
                            </div>
                        </template>

                        <template x-for="(item, index) in cart" :key="index">
                            <div class="asp-pos-line">
                                <div class="asp-pos-line-info">
                                    <p class="asp-pos-line-name" x-text="item.name"></p>
                                    <p class="asp-pos-line-meta">KES <span x-text="formatMoney(item.price)"></span> each</p>
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
                                    <button type="button" class="text-xs text-rose-500" @click="removeItem(index)">Remove</button>
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
                    </x-ui.form-field>

                    <button
                        type="submit"
                        class="asp-btn asp-btn-primary w-full justify-center !py-3"
                        x-bind:disabled="!canCheckout || checkoutSubmitting"
                    >
                        <span class="material-symbols-outlined text-lg">payments</span>
                        <span x-text="isMpesaSelected ? 'Send STK Push & Issue Receipt' : 'Complete Sale'"></span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    @include('partials.customers.quick-create-modal')

    @include('pos._modals')
</div>
