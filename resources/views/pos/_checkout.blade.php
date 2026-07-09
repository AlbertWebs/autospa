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
        initialCustomerIds: @js($customersJson->pluck('id')->values()->all()),
        customerStoreUrl: @js(route('customers.store')),
        stkPushUrl: @js(route('pos.stk-push')),
        products: @js($productsJson),
        paymentMethods: @js($paymentMethodsJson),
        defaultCustomerId: @js(old('customer_id', ($jobCardCart ?? [])['customer_id'] ?? (($jobCardCart ?? [])['customer']['id'] ?? null) ?? $customers->first()?->id ?? '')),
        oldCustomerId: @js(old('customer_id')),
        oldPaymentMethodId: @js(old('payment_method_id')),
        oldItems: @js(old('items', [])),
        jobCardCart: @js($jobCardCart ?? null),
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
        <x-ui.section-header eyebrow="Sales">
            <div class="rounded-xl border border-slate-200/80 bg-white px-4 py-2.5 dark:border-brand-border/60 dark:bg-brand-surface-high">
                <p class="font-mono text-[10px] font-semibold uppercase tracking-widest text-slate-400">Cart</p>
                <p class="font-mono text-sm font-bold text-slate-900 dark:text-white">
                    <span x-text="itemCount"></span> items · KES <span x-text="formatMoney(total)"></span>
                </p>
            </div>
        </x-ui.section-header>
    @else
        <div class="mb-3 flex items-center justify-between rounded-xl border border-slate-200/80 bg-white px-4 py-3 dark:border-brand-border/60 dark:bg-brand-surface">
            <p class="asp-page-eyebrow !mb-0">Sales</p>
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
                        <h2 class="asp-panel-title">Products</h2>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                            @if (! empty($jobCardCart))
                                Add retail products to this job card checkout. Wash services come from the job card.
                            @else
                                Sell retail and consumable products. Add wash services on a job card first.
                            @endif
                        </p>
                    </div>
                    <span class="material-symbols-outlined text-brand-primary-dim dark:text-brand-primary">storefront</span>
                </div>

                <div class="border-b border-slate-200/80 px-5 py-4 dark:border-brand-border/60">
                    <div class="asp-pos-toolbar">
                        <div class="asp-pos-search-wrap asp-pos-search-wrap--full">
                            <span class="material-symbols-outlined asp-pos-search-icon">search</span>
                            <input type="search" x-model="search" placeholder="Search products…" class="asp-pos-search" />
                        </div>
                    </div>
                </div>

                <div class="asp-pos-grid">
                    <template x-for="item in filteredItems" :key="item.itemType + '-' + item.id">
                        <button type="button" class="group asp-pos-tile" @click="addItem(item)">
                            <span class="asp-pos-tile-add material-symbols-outlined text-lg">add</span>
                            <span class="asp-pos-tile-type asp-pos-tile-type--product">product</span>
                            <span class="asp-pos-tile-name" x-text="item.name"></span>
                            <span class="asp-pos-tile-meta" x-show="item.sku" x-text="item.sku" x-cloak></span>
                            <span class="asp-pos-tile-price">KES <span x-text="formatMoney(item.price)"></span></span>
                        </button>
                    </template>
                </div>

                <div x-show="filteredItems.length === 0" x-cloak class="p-6">
                    <div class="asp-pos-empty">
                        <span class="material-symbols-outlined mb-2 text-3xl text-slate-300">inventory_2</span>
                        <p class="text-sm font-medium text-slate-600 dark:text-slate-300">No items found</p>
                        <p class="mt-1 text-xs text-slate-500">Try a different search term.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="asp-pos-checkout">
            <div class="asp-pos-cart">
                <div class="asp-pos-cart-header">
                    <div>
                        <h2 class="asp-panel-title">
                            @if (! empty($jobCardCart))
                                Checkout · Job #{{ $jobCardCart['job_card_id'] }}
                            @else
                                Checkout
                            @endif
                        </h2>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                            <span x-text="itemCount"></span> item(s) in cart
                            @if (! empty($jobCardCart['customer']['full_name']))
                                · {{ $jobCardCart['customer']['full_name'] }}
                                @if (! empty($jobCardCart['vehicle']['registration_number']))
                                    · {{ $jobCardCart['vehicle']['registration_number'] }}
                                @endif
                            @endif
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

                <form method="POST" action="{{ route('pos.store') }}" class="asp-pos-cart-body asp-form !space-y-4" data-offline-capable="true" @submit="handleCheckout">
                    @csrf

                    <input type="hidden" name="customer_id" x-model="customerId">
                    <input type="hidden" name="vehicle_id" x-bind:value="vehicleId">
                    <input type="hidden" name="job_card_id" x-bind:value="jobCardId">
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
                            <li>
                                2. Add products to the cart
                                @if (! empty($jobCardCart))
                                    (services are already on this job card)
                                @endif
                                .
                            </li>
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
                                @foreach ($customersJson as $customer)
                                    <option
                                        value="{{ $customer['id'] }}"
                                        @selected((string) old('customer_id', ($jobCardCart ?? [])['customer_id'] ?? '') === (string) $customer['id'])
                                    >{{ $customer['option_label'] }}</option>
                                @endforeach
                                <template x-for="customer in dynamicCustomers" :key="'customer-' + customer.id">
                                    <option :value="String(customer.id)" x-text="customer.option_label"></option>
                                </template>
                            </x-ui.select>
                            <button type="button" class="asp-btn asp-btn-secondary shrink-0 !px-3" title="Create new customer" @click="openCustomerModal()">
                                <span class="material-symbols-outlined text-lg">person_add</span>
                            </button>
                        </div>
                        <p
                            x-show="jobCardVehicleLabel && customerId"
                            x-cloak
                            class="mt-1 text-xs font-medium text-brand-primary-dim dark:text-brand-primary"
                        >
                            Vehicle: <span x-text="jobCardVehicleLabel"></span>
                        </p>
                        <p
                            x-show="selectedCustomerPhone && customerId"
                            x-cloak
                            class="mt-0.5 text-xs text-slate-500 dark:text-slate-400"
                        >
                            Phone: <span x-text="selectedCustomerPhone"></span>
                        </p>
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
                                    <p class="asp-pos-line-name">
                                        <span x-text="item.name"></span>
                                        <span
                                            x-show="isLockedLine(item)"
                                            x-cloak
                                            class="ml-1 rounded bg-sky-100 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-sky-700 dark:bg-sky-900 dark:text-sky-200"
                                        >Job card</span>
                                    </p>
                                    <p class="asp-pos-line-meta">KES <span x-text="formatMoney(item.price)"></span> each</p>
                                </div>
                                <div class="flex flex-col items-end gap-2">
                                    <div class="asp-pos-qty">
                                        <button type="button" class="asp-pos-qty-btn rounded-l-lg" x-bind:disabled="isLockedLine(item)" @click="decrementItem(index)">
                                            <span class="material-symbols-outlined text-base">remove</span>
                                        </button>
                                        <span class="asp-pos-qty-value" x-text="item.qty"></span>
                                        <button type="button" class="asp-pos-qty-btn rounded-r-lg" @click="incrementItem(index)">
                                            <span class="material-symbols-outlined text-base">add</span>
                                        </button>
                                    </div>
                                    <button type="button" class="text-xs text-rose-500" x-show="!isLockedLine(item)" x-cloak @click="removeItem(index)">Remove</button>
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
                            <template x-for="method in paymentMethods" :key="method.id">
                                <option
                                    :value="method.id"
                                    :disabled="!$store.offline.online && method.slug === 'mpesa'"
                                    x-text="method.name + (!$store.offline.online && method.slug === 'mpesa' ? ' (offline unavailable)' : '')"
                                ></option>
                            </template>
                        </x-ui.select>
                        <p
                            x-show="!$store.offline.online && isMpesaSelected"
                            x-cloak
                            class="mt-1 text-xs text-amber-600 dark:text-amber-400"
                        >
                            M-Pesa STK push requires an internet connection.
                        </p>
                    </x-ui.form-field>

                    <div
                        x-show="pendingReceipt"
                        x-cloak
                        class="rounded-2xl border border-amber-200/80 bg-amber-50 px-4 py-4 dark:border-amber-500/20 dark:bg-amber-500/10"
                    >
                        <p class="font-mono text-[10px] font-semibold uppercase tracking-widest text-amber-700 dark:text-amber-300">Pending Sync</p>
                        <p class="mt-2 text-sm font-medium text-slate-900 dark:text-white">
                            Sale recorded locally — receipt will be issued when synced.
                        </p>
                        <div class="mt-2 space-y-1 text-sm text-slate-600 dark:text-slate-300">
                            <p><span x-text="pendingReceipt?.itemCount"></span> item(s) · KES <span x-text="formatMoney(pendingReceipt?.total ?? 0)"></span></p>
                            <p>Payment: <span x-text="pendingReceipt?.methodName"></span></p>
                        </div>
                        <button type="button" class="asp-btn asp-btn-ghost mt-3 !px-3 !py-1.5 text-xs" @click="dismissPendingReceipt()">
                            Dismiss
                        </button>
                    </div>

                    <button
                        type="submit"
                        class="asp-btn asp-btn-primary w-full justify-center !py-3"
                        x-bind:disabled="!canCheckout || checkoutSubmitting || (!$store.offline.online && isMpesaSelected)"
                    >
                        <span class="material-symbols-outlined text-lg">payments</span>
                        <span x-text="!$store.offline.online && isMpesaSelected ? 'M-Pesa unavailable offline' : (isMpesaSelected ? 'Send STK Push & Issue Receipt' : ($store.offline.online ? 'Complete Sale' : 'Complete Sale (sync later)'))"></span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    @include('partials.customers.quick-create-modal')

    @include('pos._modals')
</div>
