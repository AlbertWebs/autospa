<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Point of Sale</h1></x-slot>

    <div class="grid gap-6 lg:grid-cols-3" x-data="{
        cart: [],
        customerId: '',
        paymentMethodId: '',
        addItem(item, type) {
            const existing = this.cart.find(i => i.id === item.id && i.type === type);
            if (existing) { existing.qty++; } else { this.cart.push({ id: item.id, type, name: item.name, price: parseFloat(item.price), qty: 1 }); }
        },
        removeItem(index) { this.cart.splice(index, 1); },
        get subtotal() { return this.cart.reduce((s, i) => s + i.price * i.qty, 0); },
        get total() { return this.subtotal; }
    }">
        <div class="lg:col-span-2 space-y-6">
            <x-ui.card :padding="false">
                <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-800">
                    <h2 class="font-semibold">Services</h2>
                </div>
                <div class="grid gap-3 p-4 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($services as $service)
                        <button type="button" @click="addItem({ id: {{ $service->id }}, name: @js($service->name), price: {{ $service->price }} }, 'service')"
                            class="rounded-xl border border-slate-200 p-4 text-left transition hover:border-indigo-300 hover:shadow-sm dark:border-slate-700">
                            <div class="font-medium">{{ $service->name }}</div>
                            <div class="text-sm text-indigo-600 dark:text-indigo-400">{{ number_format($service->price, 2) }}</div>
                        </button>
                    @endforeach
                </div>
            </x-ui.card>
            <x-ui.card :padding="false">
                <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-800">
                    <h2 class="font-semibold">Products</h2>
                </div>
                <div class="grid gap-3 p-4 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($products as $product)
                        <button type="button" @click="addItem({ id: {{ $product->id }}, name: @js($product->name), price: {{ $product->selling_price }} }, 'product')"
                            class="rounded-xl border border-slate-200 p-4 text-left transition hover:border-indigo-300 hover:shadow-sm dark:border-slate-700">
                            <div class="font-medium">{{ $product->name }}</div>
                            <div class="text-sm text-indigo-600 dark:text-indigo-400">{{ number_format($product->selling_price, 2) }}</div>
                        </button>
                    @endforeach
                </div>
            </x-ui.card>
        </div>

        <div class="lg:col-span-1">
            <x-ui.card class="sticky top-6">
                <h2 class="mb-4 text-lg font-semibold">Cart</h2>
                <form method="POST" action="{{ route('pos.store') }}">
                    @csrf
                    <input type="hidden" name="customer_id" x-model="customerId">
                    <template x-for="(item, index) in cart" :key="index">
                        <input type="hidden" :name="'items['+index+'][id]'" :value="item.id">
                        <input type="hidden" :name="'items['+index+'][type]'" :value="item.type">
                        <input type="hidden" :name="'items['+index+'][qty]'" :value="item.qty">
                    </template>

                    <div class="mb-4">
                        <x-input-label for="pos_customer" value="Customer" />
                        <select id="pos_customer" x-model="customerId" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                            <option value="">Walk-in</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->full_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4 max-h-64 space-y-2 overflow-y-auto">
                        <template x-if="cart.length === 0">
                            <p class="text-sm text-slate-500">Cart is empty. Add services or products.</p>
                        </template>
                        <template x-for="(item, index) in cart" :key="index">
                            <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2 dark:bg-slate-800">
                                <div>
                                    <div class="text-sm font-medium" x-text="item.name"></div>
                                    <div class="text-xs text-slate-500"><span x-text="item.qty"></span> × <span x-text="item.price.toFixed(2)"></span></div>
                                </div>
                                <button type="button" @click="removeItem(index)" class="text-red-500 hover:text-red-700">&times;</button>
                            </div>
                        </template>
                    </div>

                    <div class="mb-4 border-t border-slate-200 pt-4 dark:border-slate-800">
                        <div class="flex justify-between text-lg font-bold">
                            <span>Total</span>
                            <span x-text="total.toFixed(2)"></span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <x-input-label for="payment_method" value="Payment Method" />
                        <select id="payment_method" name="payment_method_id" x-model="paymentMethodId" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" required>
                            <option value="">Select…</option>
                            @foreach ($paymentMethods as $method)
                                <option value="{{ $method->id }}">{{ $method->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <x-primary-button class="w-full justify-center" x-bind:disabled="cart.length === 0">Complete Sale</x-primary-button>
                </form>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
