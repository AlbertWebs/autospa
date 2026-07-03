#!/usr/bin/env python3
"""Generate remaining AutoSpa Blade view files (part 2c: job-cards through payments)."""
import os, sys
sys.path.insert(0, os.path.dirname(__file__))
from generate_blade_views import IC, write, created, layout, create_page, edit_page, show_page, show_page_view_only

# ============ JOB CARDS ============
write("job-cards/_form.blade.php", f"""@php $jobCard = $jobCard ?? null; @endphp
<div class="grid gap-6 sm:grid-cols-2">
    <div>
        <x-input-label for="customer_id" value="Customer" />
        <select id="customer_id" name="customer_id" class="{IC}" required>
            <option value="">Select customer…</option>
            @foreach ($customers as $customer)
                <option value="{{{{ $customer->id }}}}" @selected(old('customer_id', $jobCard->customer_id ?? '') == $customer->id)>{{{{ $customer->full_name }}}}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('customer_id')" />
    </div>
    <div>
        <x-input-label for="vehicle_id" value="Vehicle" />
        <select id="vehicle_id" name="vehicle_id" class="{IC}" required>
            <option value="">Select vehicle…</option>
            @foreach ($vehicles as $vehicle)
                <option value="{{{{ $vehicle->id }}}}" @selected(old('vehicle_id', $jobCard->vehicle_id ?? '') == $vehicle->id)>{{{{ $vehicle->registration_number }}}}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('vehicle_id')" />
    </div>
    <div>
        <x-input-label for="booking_id" value="Booking" />
        <select id="booking_id" name="booking_id" class="{IC}">
            <option value="">None</option>
            @foreach ($bookings as $booking)
                <option value="{{{{ $booking->id }}}}" @selected(old('booking_id', $jobCard->booking_id ?? '') == $booking->id)>#{{{{ $booking->id }}}} — {{{{ $booking->customer?->full_name }}}}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('booking_id')" />
    </div>
    <div>
        <x-input-label for="assigned_to" value="Assigned To" />
        <select id="assigned_to" name="assigned_to" class="{IC}">
            <option value="">Unassigned</option>
            @foreach ($employees as $employee)
                <option value="{{{{ $employee->id }}}}" @selected(old('assigned_to', $jobCard->assigned_to ?? '') == $employee->id)>{{{{ $employee->full_name }}}}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('assigned_to')" />
    </div>
    <div>
        <x-input-label for="status" value="Status" />
        <select id="status" name="status" class="{IC}" required>
            @foreach (['open', 'in_progress', 'completed', 'cancelled'] as $status)
                <option value="{{{{ $status }}}}" @selected(old('status', $jobCard->status ?? 'open') == $status)>{{{{ ucfirst(str_replace('_', ' ', $status)) }}}}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('status')" />
    </div>
    <div class="sm:col-span-2">
        <x-input-label for="notes" value="Notes" />
        <textarea id="notes" name="notes" rows="3" class="{IC}">{{ old('notes', $jobCard->notes ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('notes')" />
    </div>
</div>""")

JC_ROW = """                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="whitespace-nowrap px-6 py-4 font-medium">#{{ $jobCard->id }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ $jobCard->customer?->full_name ?? '—' }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ $jobCard->vehicle?->registration_number ?? '—' }}</td>
                            <td class="whitespace-nowrap px-6 py-4"><x-ui.badge color="indigo">{{ ucfirst(str_replace('_', ' ', $jobCard->status)) }}</x-ui.badge></td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <a href="{{ route('job-cards.show', $jobCard) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">View</a>
                                <span class="mx-2 text-slate-300">|</span>
                                <a href="{{ route('job-cards.edit', $jobCard) }}" class="text-slate-600 hover:text-slate-900 dark:text-slate-400">Edit</a>
                            </td>
                        </tr>"""

def jc_index(title, empty_title, empty_desc):
    return f"""<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">{title}</h1></x-slot>
    @include('partials.crud.index-header', ['createRoute' => route('job-cards.create'), 'createLabel' => 'New Job Card'])
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white sm:hidden">{title}</h1>
    @endinclude
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Vehicle</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-500">Actions</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                    @forelse ($jobCards as $jobCard)
{JC_ROW}
                    @empty @endforelse
                </tbody>
            </table>
        </div>
        @if ($jobCards->isEmpty())<x-ui.empty-state title="{empty_title}" description="{empty_desc}" />@endif
        @include('partials.crud.pagination', ['paginator' => $jobCards])
    </x-ui.card>
</x-layouts.app>"""

write("job-cards/index.blade.php", jc_index("Job Cards", "No job cards yet", "Create a job card to track service work."))
write("job-cards/open.blade.php", jc_index("Open Job Cards", "No open job cards", "Open job cards will appear here."))
write("job-cards/in-progress.blade.php", jc_index("In Progress", "No jobs in progress", "Jobs currently being worked on will appear here."))
write("job-cards/completed.blade.php", jc_index("Completed Job Cards", "No completed jobs", "Completed job cards will appear here."))
write("job-cards/create.blade.php", create_page("New Job Card", "job-cards.store", "job-cards.index", "job-cards._form", "Create Job Card"))
write("job-cards/edit.blade.php", edit_page("Edit Job Card", "job-cards.update", "job-cards.show", "job-cards._form", "jobCard", "Save Changes"))
write("job-cards/show.blade.php", show_page("Job Card #{{ $jobCard->id }}", "job-cards.index", "job-cards.edit", "job-cards.destroy", "Delete this job card?", """    <x-ui.card>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Customer</dt><dd class="font-medium">{{ $jobCard->customer?->full_name ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Vehicle</dt><dd>{{ $jobCard->vehicle?->registration_number ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Booking</dt><dd>{{ $jobCard->booking_id ? '#'.$jobCard->booking_id : '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Assigned To</dt><dd>{{ $jobCard->employee?->full_name ?? 'Unassigned' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd><x-ui.badge color="indigo">{{ ucfirst(str_replace('_', ' ', $jobCard->status)) }}</x-ui.badge></dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Notes</dt><dd>{{ $jobCard->notes ?? '—' }}</dd></div>
        </dl>
    </x-ui.card>"""))

# ============ PRODUCTS ============
write("products/_form.blade.php", f"""@php $product = $product ?? null; @endphp
<div class="grid gap-6 sm:grid-cols-2">
    <div>
        <x-input-label for="supplier_id" value="Supplier" />
        <select id="supplier_id" name="supplier_id" class="{IC}">
            <option value="">Select supplier…</option>
            @foreach ($suppliers as $supplier)
                <option value="{{{{ $supplier->id }}}}" @selected(old('supplier_id', $product->supplier_id ?? '') == $supplier->id)>{{{{ $supplier->name }}}}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('supplier_id')" />
    </div>
    <div>
        <x-input-label for="sku" value="SKU" />
        <x-text-input id="sku" name="sku" class="{IC}" :value="old('sku', $product->sku ?? '')" required />
        <x-input-error :messages="$errors->get('sku')" />
    </div>
    <div>
        <x-input-label for="name" value="Product Name" />
        <x-text-input id="name" name="name" class="{IC}" :value="old('name', $product->name ?? '')" required />
        <x-input-error :messages="$errors->get('name')" />
    </div>
    <div>
        <x-input-label for="unit" value="Unit" />
        <x-text-input id="unit" name="unit" class="{IC}" :value="old('unit', $product->unit ?? '')" />
        <x-input-error :messages="$errors->get('unit')" />
    </div>
    <div>
        <x-input-label for="cost_price" value="Cost Price" />
        <x-text-input id="cost_price" name="cost_price" type="number" step="0.01" class="{IC}" :value="old('cost_price', $product->cost_price ?? '')" />
        <x-input-error :messages="$errors->get('cost_price')" />
    </div>
    <div>
        <x-input-label for="selling_price" value="Selling Price" />
        <x-text-input id="selling_price" name="selling_price" type="number" step="0.01" class="{IC}" :value="old('selling_price', $product->selling_price ?? '')" />
        <x-input-error :messages="$errors->get('selling_price')" />
    </div>
    <div>
        <x-input-label for="quantity_on_hand" value="Quantity on Hand" />
        <x-text-input id="quantity_on_hand" name="quantity_on_hand" type="number" class="{IC}" :value="old('quantity_on_hand', $product->quantity_on_hand ?? 0)" />
        <x-input-error :messages="$errors->get('quantity_on_hand')" />
    </div>
    <div>
        <x-input-label for="minimum_level" value="Minimum Level" />
        <x-text-input id="minimum_level" name="minimum_level" type="number" class="{IC}" :value="old('minimum_level', $product->minimum_level ?? 0)" />
        <x-input-error :messages="$errors->get('minimum_level')" />
    </div>
    <div class="sm:col-span-2">
        <x-input-label for="description" value="Description" />
        <textarea id="description" name="description" rows="3" class="{IC}">{{ old('description', $product->description ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('description')" />
    </div>
    <div class="sm:col-span-2">
        <label class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600" @checked(old('is_active', $product->is_active ?? true))>
            <span class="text-sm text-slate-700 dark:text-slate-300">Active</span>
        </label>
    </div>
</div>""")

PROD_ROW = """                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="whitespace-nowrap px-6 py-4 font-medium">{{ $product->name }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-slate-500">{{ $product->sku }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ $product->quantity_on_hand }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ number_format($product->selling_price, 2) }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <a href="{{ route('products.show', $product) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">View</a>
                                <span class="mx-2 text-slate-300">|</span>
                                <a href="{{ route('products.edit', $product) }}" class="text-slate-600 hover:text-slate-900 dark:text-slate-400">Edit</a>
                            </td>
                        </tr>"""

write("products/index.blade.php", f"""<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Products</h1></x-slot>
    @include('partials.crud.index-header', ['createRoute' => route('products.create'), 'createLabel' => 'Add Product'])
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white sm:hidden">Products</h1>
    @endinclude
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">SKU</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Stock</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Price</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-500">Actions</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                    @forelse ($products as $product)
{PROD_ROW}
                    @empty @endforelse
                </tbody>
            </table>
        </div>
        @if ($products->isEmpty())<x-ui.empty-state title="No products yet" description="Add inventory products for retail and supplies." />@endif
        @include('partials.crud.pagination', ['paginator' => $products])
    </x-ui.card>
</x-layouts.app>""")

write("products/low-stock.blade.php", f"""<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Low Stock</h1></x-slot>
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">SKU</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">On Hand</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Minimum</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-500">Actions</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                    @forelse ($products as $product)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="whitespace-nowrap px-6 py-4 font-medium">{{ $product->name }} <x-ui.badge color="red">Low</x-ui.badge></td>
                            <td class="whitespace-nowrap px-6 py-4 text-slate-500">{{ $product->sku }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-red-600">{{ $product->quantity_on_hand }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ $product->minimum_level }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <a href="{{ route('products.show', $product) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">View</a>
                            </td>
                        </tr>
                    @empty @endforelse
                </tbody>
            </table>
        </div>
        @if ($products->isEmpty())<x-ui.empty-state title="All stocked up" description="No products below minimum level." />@endif
    </x-ui.card>
</x-layouts.app>""")

write("products/create.blade.php", create_page("Add Product", "products.store", "products.index", "products._form", "Create Product"))
write("products/edit.blade.php", edit_page("Edit Product", "products.update", "products.show", "products._form", "product", "Save Changes"))
write("products/show.blade.php", show_page("{{ $product->name }}", "products.index", "products.edit", "products.destroy", "Delete this product?", """    <x-ui.card>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4"><dt class="text-slate-500">SKU</dt><dd class="font-medium">{{ $product->sku }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Supplier</dt><dd>{{ $product->supplier?->name ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Cost / Selling</dt><dd>{{ number_format($product->cost_price, 2) }} / {{ number_format($product->selling_price, 2) }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Stock</dt><dd>{{ $product->quantity_on_hand }} {{ $product->unit }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Minimum Level</dt><dd>{{ $product->minimum_level }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd>@if($product->is_active)<x-ui.badge color="green">Active</x-ui.badge>@else<x-ui.badge color="slate">Inactive</x-ui.badge>@endif</dd></div>
        </dl>
    </x-ui.card>"""))

# ============ SUPPLIERS ============
write("suppliers/_form.blade.php", f"""@php $supplier = $supplier ?? null; @endphp
<div class="grid gap-6 sm:grid-cols-2">
    <div>
        <x-input-label for="name" value="Supplier Name" />
        <x-text-input id="name" name="name" class="{IC}" :value="old('name', $supplier->name ?? '')" required />
        <x-input-error :messages="$errors->get('name')" />
    </div>
    <div>
        <x-input-label for="contact_person" value="Contact Person" />
        <x-text-input id="contact_person" name="contact_person" class="{IC}" :value="old('contact_person', $supplier->contact_person ?? '')" />
        <x-input-error :messages="$errors->get('contact_person')" />
    </div>
    <div>
        <x-input-label for="phone" value="Phone" />
        <x-text-input id="phone" name="phone" class="{IC}" :value="old('phone', $supplier->phone ?? '')" />
        <x-input-error :messages="$errors->get('phone')" />
    </div>
    <div>
        <x-input-label for="email" value="Email" />
        <x-text-input id="email" name="email" type="email" class="{IC}" :value="old('email', $supplier->email ?? '')" />
        <x-input-error :messages="$errors->get('email')" />
    </div>
    <div class="sm:col-span-2">
        <x-input-label for="address" value="Address" />
        <textarea id="address" name="address" rows="2" class="{IC}">{{ old('address', $supplier->address ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('address')" />
    </div>
    <div class="sm:col-span-2">
        <label class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600" @checked(old('is_active', $supplier->is_active ?? true))>
            <span class="text-sm text-slate-700 dark:text-slate-300">Active</span>
        </label>
    </div>
</div>""")

write("suppliers/index.blade.php", """<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Suppliers</h1></x-slot>
    @include('partials.crud.index-header', ['createRoute' => route('suppliers.create'), 'createLabel' => 'Add Supplier'])
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white sm:hidden">Suppliers</h1>
    @endinclude
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Contact</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Phone</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-500">Actions</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                    @forelse ($suppliers as $supplier)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="whitespace-nowrap px-6 py-4 font-medium">{{ $supplier->name }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-slate-500">{{ $supplier->contact_person ?? '—' }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ $supplier->phone ?? '—' }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <a href="{{ route('suppliers.show', $supplier) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">View</a>
                                <span class="mx-2 text-slate-300">|</span>
                                <a href="{{ route('suppliers.edit', $supplier) }}" class="text-slate-600 hover:text-slate-900 dark:text-slate-400">Edit</a>
                            </td>
                        </tr>
                    @empty @endforelse
                </tbody>
            </table>
        </div>
        @if ($suppliers->isEmpty())<x-ui.empty-state title="No suppliers yet" description="Add suppliers for inventory purchases." />@endif
        @include('partials.crud.pagination', ['paginator' => $suppliers])
    </x-ui.card>
</x-layouts.app>""")

write("suppliers/create.blade.php", create_page("Add Supplier", "suppliers.store", "suppliers.index", "suppliers._form", "Create Supplier"))
write("suppliers/edit.blade.php", edit_page("Edit Supplier", "suppliers.update", "suppliers.show", "suppliers._form", "supplier", "Save Changes"))
write("suppliers/show.blade.php", show_page("{{ $supplier->name }}", "suppliers.index", "suppliers.edit", "suppliers.destroy", "Delete this supplier?", """    <x-ui.card>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Contact Person</dt><dd>{{ $supplier->contact_person ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Phone</dt><dd>{{ $supplier->phone ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Email</dt><dd>{{ $supplier->email ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Address</dt><dd>{{ $supplier->address ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd>@if($supplier->is_active)<x-ui.badge color="green">Active</x-ui.badge>@else<x-ui.badge color="slate">Inactive</x-ui.badge>@endif</dd></div>
        </dl>
    </x-ui.card>"""))

# ============ PURCHASE ORDERS ============
write("purchase-orders/_form.blade.php", f"""@php $purchaseOrder = $purchaseOrder ?? null; @endphp
<div class="grid gap-6 sm:grid-cols-2">
    <div>
        <x-input-label for="supplier_id" value="Supplier" />
        <select id="supplier_id" name="supplier_id" class="{IC}" required>
            <option value="">Select supplier…</option>
            @foreach ($suppliers as $supplier)
                <option value="{{{{ $supplier->id }}}}" @selected(old('supplier_id', $purchaseOrder->supplier_id ?? '') == $supplier->id)>{{{{ $supplier->name }}}}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('supplier_id')" />
    </div>
    <div>
        <x-input-label for="reference" value="Reference" />
        <x-text-input id="reference" name="reference" class="{IC}" :value="old('reference', $purchaseOrder->reference ?? '')" />
        <x-input-error :messages="$errors->get('reference')" />
    </div>
    <div>
        <x-input-label for="status" value="Status" />
        <select id="status" name="status" class="{IC}" required>
            @foreach (['draft', 'ordered', 'received', 'cancelled'] as $status)
                <option value="{{{{ $status }}}}" @selected(old('status', $purchaseOrder->status ?? 'draft') == $status)>{{{{ ucfirst($status) }}}}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('status')" />
    </div>
    <div class="sm:col-span-2">
        <x-input-label for="notes" value="Notes" />
        <textarea id="notes" name="notes" rows="3" class="{IC}">{{ old('notes', $purchaseOrder->notes ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('notes')" />
    </div>
</div>""")

write("purchase-orders/index.blade.php", """<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Purchase Orders</h1></x-slot>
    @include('partials.crud.index-header', ['createRoute' => route('purchase-orders.create'), 'createLabel' => 'New Purchase Order'])
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white sm:hidden">Purchase Orders</h1>
    @endinclude
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Reference</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Supplier</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-500">Actions</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                    @forelse ($purchaseOrders as $purchaseOrder)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="whitespace-nowrap px-6 py-4 font-medium">{{ $purchaseOrder->reference ?? '#'.$purchaseOrder->id }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ $purchaseOrder->supplier?->name ?? '—' }}</td>
                            <td class="whitespace-nowrap px-6 py-4"><x-ui.badge color="indigo">{{ ucfirst($purchaseOrder->status) }}</x-ui.badge></td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">View</a>
                                <span class="mx-2 text-slate-300">|</span>
                                <a href="{{ route('purchase-orders.edit', $purchaseOrder) }}" class="text-slate-600 hover:text-slate-900 dark:text-slate-400">Edit</a>
                            </td>
                        </tr>
                    @empty @endforelse
                </tbody>
            </table>
        </div>
        @if ($purchaseOrders->isEmpty())<x-ui.empty-state title="No purchase orders" description="Create purchase orders to restock inventory." />@endif
        @include('partials.crud.pagination', ['paginator' => $purchaseOrders])
    </x-ui.card>
</x-layouts.app>""")

write("purchase-orders/create.blade.php", create_page("New Purchase Order", "purchase-orders.store", "purchase-orders.index", "purchase-orders._form", "Create Purchase Order"))
write("purchase-orders/edit.blade.php", edit_page("Edit Purchase Order", "purchase-orders.update", "purchase-orders.show", "purchase-orders._form", "purchaseOrder", "Save Changes"))
write("purchase-orders/show.blade.php", show_page("PO {{ $purchaseOrder->reference ?? '#'.$purchaseOrder->id }}", "purchase-orders.index", "purchase-orders.edit", "purchase-orders.destroy", "Delete this purchase order?", """    <x-ui.card>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Supplier</dt><dd class="font-medium">{{ $purchaseOrder->supplier?->name ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd><x-ui.badge color="indigo">{{ ucfirst($purchaseOrder->status) }}</x-ui.badge></dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Notes</dt><dd>{{ $purchaseOrder->notes ?? '—' }}</dd></div>
        </dl>
    </x-ui.card>"""))

# ============ STOCK MOVEMENTS ============
write("stock-movements/_form.blade.php", f"""@php $movement = $movement ?? null; @endphp
<div class="grid gap-6 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <x-input-label for="product_id" value="Product" />
        <select id="product_id" name="product_id" class="{IC}" required>
            <option value="">Select product…</option>
            @foreach ($products as $product)
                <option value="{{{{ $product->id }}}}" @selected(old('product_id', $movement->product_id ?? '') == $product->id)>{{{{ $product->name }}}} ({{{{ $product->sku }}}})</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('product_id')" />
    </div>
    <div>
        <x-input-label for="type" value="Type" />
        <select id="type" name="type" class="{IC}" required>
            @foreach (['in', 'out', 'adjustment'] as $type)
                <option value="{{{{ $type }}}}" @selected(old('type', $movement->type ?? 'in') == $type)>{{{{ ucfirst($type) }}}}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('type')" />
    </div>
    <div>
        <x-input-label for="quantity" value="Quantity" />
        <x-text-input id="quantity" name="quantity" type="number" class="{IC}" :value="old('quantity', $movement->quantity ?? '')" required />
        <x-input-error :messages="$errors->get('quantity')" />
    </div>
    <div class="sm:col-span-2">
        <x-input-label for="notes" value="Notes" />
        <textarea id="notes" name="notes" rows="3" class="{IC}">{{ old('notes', $movement->notes ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('notes')" />
    </div>
</div>""")

write("stock-movements/index.blade.php", """<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Stock Movements</h1></x-slot>
    @include('partials.crud.index-header', ['createRoute' => route('stock-movements.create'), 'createLabel' => 'Record Movement'])
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white sm:hidden">Stock Movements</h1>
    @endinclude
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Product</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Quantity</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Date</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-500">Actions</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                    @forelse ($movements as $movement)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="whitespace-nowrap px-6 py-4 font-medium">{{ $movement->product?->name ?? '—' }}</td>
                            <td class="whitespace-nowrap px-6 py-4"><x-ui.badge color="indigo">{{ ucfirst($movement->type) }}</x-ui.badge></td>
                            <td class="whitespace-nowrap px-6 py-4">{{ $movement->quantity }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-slate-500">{{ $movement->created_at?->format('M j, Y') }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <a href="{{ route('stock-movements.show', $movement) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">View</a>
                            </td>
                        </tr>
                    @empty @endforelse
                </tbody>
            </table>
        </div>
        @if ($movements->isEmpty())<x-ui.empty-state title="No stock movements" description="Record stock in/out movements here." />@endif
        @include('partials.crud.pagination', ['paginator' => $movements])
    </x-ui.card>
</x-layouts.app>""")

write("stock-movements/create.blade.php", create_page("Record Stock Movement", "stock-movements.store", "stock-movements.index", "stock-movements._form", "Record Movement"))
write("stock-movements/show.blade.php", show_page_view_only("Stock Movement", "stock-movements.index", """    <x-ui.card>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Product</dt><dd class="font-medium">{{ $movement->product?->name ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Type</dt><dd><x-ui.badge color="indigo">{{ ucfirst($movement->type) }}</x-ui.badge></dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Quantity</dt><dd>{{ $movement->quantity }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Notes</dt><dd>{{ $movement->notes ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Date</dt><dd>{{ $movement->created_at?->format('M j, Y g:i A') }}</dd></div>
        </dl>
    </x-ui.card>"""))

# ============ POS ============
write("pos/index.blade.php", """<x-layouts.app>
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
</x-layouts.app>""")

# ============ INVOICES, RECEIPTS, REFUNDS ============
write("invoices/index.blade.php", """<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Invoices</h1></x-slot>
    @include('partials.crud.index-header', ['createRoute' => route('invoices.create'), 'createLabel' => 'New Invoice'])
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white sm:hidden">Invoices</h1>
    @endinclude
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Invoice #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-500">Actions</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                    @forelse ($invoices as $invoice)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="whitespace-nowrap px-6 py-4 font-medium">{{ $invoice->number ?? '#'.$invoice->id }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ $invoice->customer?->full_name ?? '—' }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ number_format($invoice->total ?? 0, 2) }}</td>
                            <td class="whitespace-nowrap px-6 py-4"><x-ui.badge color="indigo">{{ $invoice->status ?? 'pending' }}</x-ui.badge></td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <a href="{{ route('invoices.show', $invoice) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">View</a>
                            </td>
                        </tr>
                    @empty @endforelse
                </tbody>
            </table>
        </div>
        @if ($invoices->isEmpty())<x-ui.empty-state title="No invoices yet" description="Invoices from sales will appear here." />@endif
        @include('partials.crud.pagination', ['paginator' => $invoices])
    </x-ui.card>
</x-layouts.app>""")

write("invoices/show.blade.php", show_page_view_only("Invoice {{ $invoice->number ?? '#'.$invoice->id }}", "invoices.index", """    <x-ui.card>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Customer</dt><dd class="font-medium">{{ $invoice->customer?->full_name ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Total</dt><dd class="font-medium">{{ number_format($invoice->total ?? 0, 2) }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd><x-ui.badge color="indigo">{{ $invoice->status ?? 'pending' }}</x-ui.badge></dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Date</dt><dd>{{ $invoice->created_at?->format('M j, Y') }}</dd></div>
        </dl>
    </x-ui.card>"""))

write("receipts/index.blade.php", """<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Receipts</h1></x-slot>
    @include('partials.crud.index-header', ['createRoute' => null, 'createLabel' => ''])
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white sm:hidden">Receipts</h1>
    @endinclude
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Receipt #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Amount</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-500">Actions</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                    @forelse ($receipts as $receipt)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="whitespace-nowrap px-6 py-4 font-medium">{{ $receipt->number ?? '#'.$receipt->id }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ $receipt->customer?->full_name ?? '—' }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ number_format($receipt->total ?? 0, 2) }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <a href="{{ route('receipts.show', $receipt) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">View</a>
                            </td>
                        </tr>
                    @empty @endforelse
                </tbody>
            </table>
        </div>
        @if ($receipts->isEmpty())<x-ui.empty-state title="No receipts yet" description="Payment receipts will appear here." />@endif
        @include('partials.crud.pagination', ['paginator' => $receipts])
    </x-ui.card>
</x-layouts.app>""")

write("receipts/show.blade.php", show_page_view_only("Receipt {{ $receipt->number ?? '#'.$receipt->id }}", "receipts.index", """    <x-ui.card>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Customer</dt><dd class="font-medium">{{ $receipt->customer?->full_name ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Amount</dt><dd class="font-medium">{{ number_format($receipt->total ?? 0, 2) }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Date</dt><dd>{{ $receipt->created_at?->format('M j, Y g:i A') }}</dd></div>
        </dl>
    </x-ui.card>"""))

write("refunds/index.blade.php", """<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Refunds</h1></x-slot>
    @include('partials.crud.index-header', ['createRoute' => route('refunds.create'), 'createLabel' => 'New Refund'])
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white sm:hidden">Refunds</h1>
    @endinclude
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Refund #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-500">Actions</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                    @forelse ($refunds as $refund)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="whitespace-nowrap px-6 py-4 font-medium">#{{ $refund->id }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ number_format($refund->amount ?? 0, 2) }}</td>
                            <td class="whitespace-nowrap px-6 py-4"><x-ui.badge color="indigo">{{ $refund->status ?? 'pending' }}</x-ui.badge></td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <a href="{{ route('refunds.show', $refund) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">View</a>
                            </td>
                        </tr>
                    @empty @endforelse
                </tbody>
            </table>
        </div>
        @if ($refunds->isEmpty())<x-ui.empty-state title="No refunds" description="Refund records will appear here." />@endif
        @include('partials.crud.pagination', ['paginator' => $refunds])
    </x-ui.card>
</x-layouts.app>""")

write("refunds/show.blade.php", show_page_view_only("Refund #{{ $refund->id }}", "refunds.index", """    <x-ui.card>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Amount</dt><dd class="font-medium">{{ number_format($refund->amount ?? 0, 2) }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd><x-ui.badge color="indigo">{{ $refund->status ?? 'pending' }}</x-ui.badge></dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Reason</dt><dd>{{ $refund->reason ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Date</dt><dd>{{ $refund->created_at?->format('M j, Y') }}</dd></div>
        </dl>
    </x-ui.card>"""))

# ============ PAYMENTS ============
PAY_ROW = """                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="whitespace-nowrap px-6 py-4 font-medium">#{{ $payment->id }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ $payment->customer?->full_name ?? '—' }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ number_format($payment->amount ?? 0, 2) }}</td>
                            <td class="whitespace-nowrap px-6 py-4"><x-ui.badge color="indigo">{{ $payment->method ?? $payment->payment_method?->name ?? '—' }}</x-ui.badge></td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <a href="{{ route('payments.show', $payment) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">View</a>
                            </td>
                        </tr>"""

def payments_index(title):
    return f"""<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">{title}</h1></x-slot>
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Payment #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Method</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-500">Actions</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                    @forelse ($payments as $payment)
{PAY_ROW}
                    @empty @endforelse
                </tbody>
            </table>
        </div>
        @if ($payments->isEmpty())<x-ui.empty-state title="No payments" description="Payment records will appear here." />@endif
        @include('partials.crud.pagination', ['paginator' => $payments])
    </x-ui.card>
</x-layouts.app>"""

write("payments/index.blade.php", payments_index("Payments"))
write("payments/cash.blade.php", payments_index("Cash Payments"))
write("payments/mpesa.blade.php", payments_index("M-Pesa Payments"))
write("payments/card.blade.php", payments_index("Card Payments"))
write("payments/bank.blade.php", payments_index("Bank Transfer Payments"))
write("payments/show.blade.php", show_page_view_only("Payment #{{ $payment->id }}", "payments.index", """    <x-ui.card>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Customer</dt><dd class="font-medium">{{ $payment->customer?->full_name ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Amount</dt><dd class="font-medium">{{ number_format($payment->amount ?? 0, 2) }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Method</dt><dd><x-ui.badge color="indigo">{{ $payment->method ?? $payment->payment_method?->name ?? '—' }}</x-ui.badge></dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Reference</dt><dd>{{ $payment->reference ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Date</dt><dd>{{ $payment->created_at?->format('M j, Y g:i A') }}</dd></div>
        </dl>
    </x-ui.card>"""))

print(f"Part 2c done, total: {len(created)}")
