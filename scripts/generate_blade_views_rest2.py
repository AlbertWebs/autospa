#!/usr/bin/env python3
"""Generate remaining AutoSpa Blade view files (part 2b: vehicles through bookings)."""
import os, sys
sys.path.insert(0, os.path.dirname(__file__))
from generate_blade_views import IC, write, created, layout, create_page, edit_page, show_page

# ============ VEHICLES ============
write("vehicles/_form.blade.php", f"""@php $vehicle = $vehicle ?? null; @endphp
<div class="grid gap-6 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <x-input-label for="customer_id" value="Customer" />
        <select id="customer_id" name="customer_id" class="{IC}" required>
            <option value="">Select customer…</option>
            @foreach ($customers as $customer)
                <option value="{{{{ $customer->id }}}}" @selected(old('customer_id', $vehicle->customer_id ?? '') == $customer->id)>{{{{ $customer->full_name }}}}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('customer_id')" />
    </div>
    <div>
        <x-input-label for="registration_number" value="Registration Number" />
        <x-text-input id="registration_number" name="registration_number" class="{IC}" :value="old('registration_number', $vehicle->registration_number ?? '')" required />
        <x-input-error :messages="$errors->get('registration_number')" />
    </div>
    <div>
        <x-input-label for="make" value="Make" />
        <x-text-input id="make" name="make" class="{IC}" :value="old('make', $vehicle->make ?? '')" required />
        <x-input-error :messages="$errors->get('make')" />
    </div>
    <div>
        <x-input-label for="model" value="Model" />
        <x-text-input id="model" name="model" class="{IC}" :value="old('model', $vehicle->model ?? '')" required />
        <x-input-error :messages="$errors->get('model')" />
    </div>
    <div>
        <x-input-label for="year" value="Year" />
        <x-text-input id="year" name="year" type="number" class="{IC}" :value="old('year', $vehicle->year ?? '')" />
        <x-input-error :messages="$errors->get('year')" />
    </div>
    <div>
        <x-input-label for="color" value="Color" />
        <x-text-input id="color" name="color" class="{IC}" :value="old('color', $vehicle->color ?? '')" />
        <x-input-error :messages="$errors->get('color')" />
    </div>
    <div>
        <x-input-label for="vin" value="VIN" />
        <x-text-input id="vin" name="vin" class="{IC}" :value="old('vin', $vehicle->vin ?? '')" />
        <x-input-error :messages="$errors->get('vin')" />
    </div>
    <div>
        <x-input-label for="mileage" value="Mileage" />
        <x-text-input id="mileage" name="mileage" type="number" class="{IC}" :value="old('mileage', $vehicle->mileage ?? '')" />
        <x-input-error :messages="$errors->get('mileage')" />
    </div>
    <div>
        <x-input-label for="status" value="Status" />
        <select id="status" name="status" class="{IC}">
            @foreach (['active', 'in_service', 'ready', 'inactive'] as $status)
                <option value="{{{{ $status }}}}" @selected(old('status', $vehicle->status ?? 'active') == $status)>{{{{ ucfirst(str_replace('_', ' ', $status)) }}}}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('status')" />
    </div>
</div>""")

VEHICLE_ROW = """                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="whitespace-nowrap px-6 py-4 font-medium">{{ $vehicle->registration_number }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ $vehicle->make }} {{ $vehicle->model }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ $vehicle->customer?->full_name ?? '—' }}</td>
                            <td class="whitespace-nowrap px-6 py-4"><x-ui.badge color="indigo">{{ ucfirst(str_replace('_', ' ', $vehicle->status)) }}</x-ui.badge></td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <a href="{{ route('vehicles.show', $vehicle) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">View</a>
                                <span class="mx-2 text-slate-300">|</span>
                                <a href="{{ route('vehicles.edit', $vehicle) }}" class="text-slate-600 hover:text-slate-900 dark:text-slate-400">Edit</a>
                            </td>
                        </tr>"""

def vehicle_index(title, empty_title, empty_desc):
    return f"""<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">{title}</h1></x-slot>
    @include('partials.crud.index-header', ['createRoute' => route('vehicles.create'), 'createLabel' => 'Add Vehicle'])
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white sm:hidden">{title}</h1>
    @endinclude
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Registration</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Vehicle</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-500">Actions</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                    @forelse ($vehicles as $vehicle)
{VEHICLE_ROW}
                    @empty @endforelse
                </tbody>
            </table>
        </div>
        @if ($vehicles->isEmpty())<x-ui.empty-state title="{empty_title}" description="{empty_desc}" />@endif
        @include('partials.crud.pagination', ['paginator' => $vehicles])
    </x-ui.card>
</x-layouts.app>"""

write("vehicles/index.blade.php", vehicle_index("Vehicles", "No vehicles yet", "Register customer vehicles for service tracking."))
write("vehicles/check-in.blade.php", vehicle_index("Vehicle Check-In", "No vehicles to check in", "Vehicles awaiting check-in will appear here."))
write("vehicles/active.blade.php", vehicle_index("Active Vehicles", "No active vehicles", "Vehicles currently in service will appear here."))
write("vehicles/ready.blade.php", vehicle_index("Ready for Pickup", "No vehicles ready", "Completed vehicles awaiting pickup will appear here."))
write("vehicles/create.blade.php", create_page("Add Vehicle", "vehicles.store", "vehicles.index", "vehicles._form", "Create Vehicle"))
write("vehicles/edit.blade.php", edit_page("Edit Vehicle", "vehicles.update", "vehicles.show", "vehicles._form", "vehicle", "Save Changes"))
write("vehicles/show.blade.php", show_page("{{ $vehicle->registration_number }}", "vehicles.index", "vehicles.edit", "vehicles.destroy", "Delete this vehicle?", """    <div class="grid gap-6 lg:grid-cols-2">
        <x-ui.card>
            <h2 class="mb-4 text-lg font-semibold">Vehicle Details</h2>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Make / Model</dt><dd class="font-medium">{{ $vehicle->make }} {{ $vehicle->model }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Year</dt><dd>{{ $vehicle->year ?? '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Color</dt><dd>{{ $vehicle->color ?? '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">VIN</dt><dd>{{ $vehicle->vin ?? '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Mileage</dt><dd>{{ $vehicle->mileage ?? '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd><x-ui.badge color="indigo">{{ ucfirst(str_replace('_', ' ', $vehicle->status)) }}</x-ui.badge></dd></div>
            </dl>
        </x-ui.card>
        <x-ui.card>
            <h2 class="mb-4 text-lg font-semibold">Owner</h2>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Customer</dt><dd class="font-medium">{{ $vehicle->customer?->full_name ?? '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Phone</dt><dd>{{ $vehicle->customer?->phone ?? '—' }}</dd></div>
            </dl>
        </x-ui.card>
    </div>"""))
write("vehicles/history.blade.php", layout("Vehicle History", """    <x-ui.card class="mb-6">
        <dl class="grid gap-4 sm:grid-cols-3 text-sm">
            <div><dt class="text-slate-500">Registration</dt><dd class="font-medium">{{ $vehicle->registration_number }}</dd></div>
            <div><dt class="text-slate-500">Vehicle</dt><dd>{{ $vehicle->make }} {{ $vehicle->model }}</dd></div>
            <div><dt class="text-slate-500">Customer</dt><dd>{{ $vehicle->customer?->full_name ?? '—' }}</dd></div>
        </dl>
    </x-ui.card>
    <div class="grid gap-6 lg:grid-cols-2">
        <x-ui.card :padding="false">
            <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-800"><h2 class="font-semibold">Job Cards</h2></div>
            <div class="divide-y divide-slate-200 dark:divide-slate-800">
                @forelse ($vehicle->jobCards ?? [] as $jobCard)
                    <a href="{{ route('job-cards.show', $jobCard) }}" class="block px-6 py-4 hover:bg-slate-50 dark:hover:bg-slate-800/50">
                        <div class="flex justify-between"><span class="font-medium">#{{ $jobCard->id }}</span><x-ui.badge color="indigo">{{ $jobCard->status }}</x-ui.badge></div>
                        <p class="text-sm text-slate-500">{{ $jobCard->created_at?->format('M j, Y') }}</p>
                    </a>
                @empty
                    <x-ui.empty-state title="No job cards" description="No service history for this vehicle." />
                @endforelse
            </div>
        </x-ui.card>
        <x-ui.card :padding="false">
            <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-800"><h2 class="font-semibold">Bookings</h2></div>
            <div class="divide-y divide-slate-200 dark:divide-slate-800">
                @forelse ($vehicle->bookings ?? [] as $booking)
                    <a href="{{ route('bookings.show', $booking) }}" class="block px-6 py-4 hover:bg-slate-50 dark:hover:bg-slate-800/50">
                        <div class="flex justify-between"><span class="font-medium">{{ $booking->scheduled_at?->format('M j, Y g:i A') }}</span><x-ui.badge color="indigo">{{ $booking->status }}</x-ui.badge></div>
                    </a>
                @empty
                    <x-ui.empty-state title="No bookings" description="No bookings for this vehicle." />
                @endforelse
            </div>
        </x-ui.card>
    </div>"""))

# ============ SERVICE CATEGORIES ============
write("service-categories/_form.blade.php", f"""@php $category = $category ?? null; @endphp
<div class="grid gap-6 sm:grid-cols-2">
    <div>
        <x-input-label for="name" value="Name" />
        <x-text-input id="name" name="name" class="{IC}" :value="old('name', $category->name ?? '')" required />
        <x-input-error :messages="$errors->get('name')" />
    </div>
    <div>
        <x-input-label for="sort_order" value="Sort Order" />
        <x-text-input id="sort_order" name="sort_order" type="number" class="{IC}" :value="old('sort_order', $category->sort_order ?? 0)" />
        <x-input-error :messages="$errors->get('sort_order')" />
    </div>
    <div class="sm:col-span-2">
        <x-input-label for="description" value="Description" />
        <textarea id="description" name="description" rows="3" class="{IC}">{{ old('description', $category->description ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('description')" />
    </div>
    <div class="sm:col-span-2">
        <label class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600" @checked(old('is_active', $category->is_active ?? true))>
            <span class="text-sm text-slate-700 dark:text-slate-300">Active</span>
        </label>
    </div>
</div>""")

write("service-categories/index.blade.php", """<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Service Categories</h1></x-slot>
    @include('partials.crud.index-header', ['createRoute' => route('services.categories.create'), 'createLabel' => 'Add Category'])
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white sm:hidden">Service Categories</h1>
    @endinclude
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Sort</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-500">Actions</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                    @forelse ($categories as $category)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="whitespace-nowrap px-6 py-4 font-medium">{{ $category->name }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-slate-500">{{ $category->sort_order }}</td>
                            <td class="whitespace-nowrap px-6 py-4">@if($category->is_active)<x-ui.badge color="green">Active</x-ui.badge>@else<x-ui.badge color="slate">Inactive</x-ui.badge>@endif</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <a href="{{ route('services.categories.show', $category) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">View</a>
                                <span class="mx-2 text-slate-300">|</span>
                                <a href="{{ route('services.categories.edit', $category) }}" class="text-slate-600 hover:text-slate-900 dark:text-slate-400">Edit</a>
                            </td>
                        </tr>
                    @empty @endforelse
                </tbody>
            </table>
        </div>
        @if ($categories->isEmpty())<x-ui.empty-state title="No categories yet" description="Organize your services into categories." />@endif
        @include('partials.crud.pagination', ['paginator' => $categories])
    </x-ui.card>
</x-layouts.app>""")

write("service-categories/create.blade.php", create_page("Add Category", "services.categories.store", "services.categories.index", "service-categories._form", "Create Category"))
write("service-categories/edit.blade.php", edit_page("Edit Category", "services.categories.update", "services.categories.show", "service-categories._form", "category", "Save Changes"))
write("service-categories/show.blade.php", show_page("{{ $category->name }}", "services.categories.index", "services.categories.edit", "services.categories.destroy", "Delete this category?", """    <x-ui.card>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Description</dt><dd>{{ $category->description ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Sort Order</dt><dd>{{ $category->sort_order }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd>@if($category->is_active)<x-ui.badge color="green">Active</x-ui.badge>@else<x-ui.badge color="slate">Inactive</x-ui.badge>@endif</dd></div>
        </dl>
    </x-ui.card>"""))

# ============ SERVICES ============
write("services/_form.blade.php", f"""@php $service = $service ?? null; @endphp
<div class="grid gap-6 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <x-input-label for="service_category_id" value="Category" />
        <select id="service_category_id" name="service_category_id" class="{IC}" required>
            <option value="">Select category…</option>
            @foreach ($categories as $category)
                <option value="{{{{ $category->id }}}}" @selected(old('service_category_id', $service->service_category_id ?? '') == $category->id)>{{{{ $category->name }}}}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('service_category_id')" />
    </div>
    <div>
        <x-input-label for="name" value="Service Name" />
        <x-text-input id="name" name="name" class="{IC}" :value="old('name', $service->name ?? '')" required />
        <x-input-error :messages="$errors->get('name')" />
    </div>
    <div>
        <x-input-label for="price" value="Price" />
        <x-text-input id="price" name="price" type="number" step="0.01" class="{IC}" :value="old('price', $service->price ?? '')" required />
        <x-input-error :messages="$errors->get('price')" />
    </div>
    <div>
        <x-input-label for="duration_minutes" value="Duration (minutes)" />
        <x-text-input id="duration_minutes" name="duration_minutes" type="number" class="{IC}" :value="old('duration_minutes', $service->duration_minutes ?? '')" required />
        <x-input-error :messages="$errors->get('duration_minutes')" />
    </div>
    <div class="sm:col-span-2">
        <x-input-label for="description" value="Description" />
        <textarea id="description" name="description" rows="3" class="{IC}">{{ old('description', $service->description ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('description')" />
    </div>
    <div class="sm:col-span-2">
        <label class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600" @checked(old('is_active', $service->is_active ?? true))>
            <span class="text-sm text-slate-700 dark:text-slate-300">Active</span>
        </label>
    </div>
</div>""")

write("services/index.blade.php", """<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Services</h1></x-slot>
    @include('partials.crud.index-header', ['createRoute' => route('services.create'), 'createLabel' => 'Add Service'])
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white sm:hidden">Services</h1>
    @endinclude
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Duration</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-500">Actions</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                    @forelse ($services as $service)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="whitespace-nowrap px-6 py-4 font-medium">{{ $service->name }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-slate-500">{{ $service->category?->name ?? '—' }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ number_format($service->price, 2) }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ $service->duration_minutes }} min</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <a href="{{ route('services.show', $service) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">View</a>
                                <span class="mx-2 text-slate-300">|</span>
                                <a href="{{ route('services.edit', $service) }}" class="text-slate-600 hover:text-slate-900 dark:text-slate-400">Edit</a>
                            </td>
                        </tr>
                    @empty @endforelse
                </tbody>
            </table>
        </div>
        @if ($services->isEmpty())<x-ui.empty-state title="No services yet" description="Add services your spa offers." />@endif
        @include('partials.crud.pagination', ['paginator' => $services])
    </x-ui.card>
</x-layouts.app>""")

write("services/create.blade.php", create_page("Add Service", "services.store", "services.index", "services._form", "Create Service"))
write("services/edit.blade.php", edit_page("Edit Service", "services.update", "services.show", "services._form", "service", "Save Changes"))
write("services/show.blade.php", show_page("{{ $service->name }}", "services.index", "services.edit", "services.destroy", "Delete this service?", """    <x-ui.card>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Category</dt><dd>{{ $service->category?->name ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Price</dt><dd class="font-medium">{{ number_format($service->price, 2) }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Duration</dt><dd>{{ $service->duration_minutes }} minutes</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd>@if($service->is_active)<x-ui.badge color="green">Active</x-ui.badge>@else<x-ui.badge color="slate">Inactive</x-ui.badge>@endif</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Description</dt><dd>{{ $service->description ?? '—' }}</dd></div>
        </dl>
    </x-ui.card>"""))
write("services/pricing.blade.php", layout("Service Pricing", """    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Service</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Duration</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                    @forelse ($services as $service)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="whitespace-nowrap px-6 py-4 font-medium">{{ $service->name }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-slate-500">{{ $service->category?->name ?? '—' }}</td>
                            <td class="whitespace-nowrap px-6 py-4 font-medium">{{ number_format($service->price, 2) }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ $service->duration_minutes }} min</td>
                        </tr>
                    @empty @endforelse
                </tbody>
            </table>
        </div>
        @if ($services->isEmpty())<x-ui.empty-state title="No services" description="Add services to view pricing." />@endif
    </x-ui.card>"""))

# ============ PACKAGES ============
write("packages/_form.blade.php", f"""@php $package = $package ?? null; @endphp
<div class="grid gap-6 sm:grid-cols-2">
    <div>
        <x-input-label for="name" value="Package Name" />
        <x-text-input id="name" name="name" class="{IC}" :value="old('name', $package->name ?? '')" required />
        <x-input-error :messages="$errors->get('name')" />
    </div>
    <div>
        <x-input-label for="price" value="Price" />
        <x-text-input id="price" name="price" type="number" step="0.01" class="{IC}" :value="old('price', $package->price ?? '')" required />
        <x-input-error :messages="$errors->get('price')" />
    </div>
    <div>
        <x-input-label for="duration_minutes" value="Duration (minutes)" />
        <x-text-input id="duration_minutes" name="duration_minutes" type="number" class="{IC}" :value="old('duration_minutes', $package->duration_minutes ?? '')" />
        <x-input-error :messages="$errors->get('duration_minutes')" />
    </div>
    <div class="sm:col-span-2">
        <x-input-label for="description" value="Description" />
        <textarea id="description" name="description" rows="3" class="{IC}">{{ old('description', $package->description ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('description')" />
    </div>
    <div class="sm:col-span-2">
        <x-input-label value="Included Services" />
        <div class="mt-2 flex flex-wrap gap-3">
            @foreach ($services as $service)
                <label class="flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 dark:border-slate-700">
                    <input type="checkbox" name="services[]" value="{{{{ $service->id }}}}" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600" @checked(in_array($service->id, old('services', $package?->services->pluck('id')->toArray() ?? [])))>
                    <span class="text-sm">{{{{ $service->name }}}}</span>
                </label>
            @endforeach
        </div>
        <x-input-error :messages="$errors->get('services')" />
    </div>
    <div class="sm:col-span-2">
        <label class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600" @checked(old('is_active', $package->is_active ?? true))>
            <span class="text-sm text-slate-700 dark:text-slate-300">Active</span>
        </label>
    </div>
</div>""")

write("packages/index.blade.php", """<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Packages</h1></x-slot>
    @include('partials.crud.index-header', ['createRoute' => route('packages.create'), 'createLabel' => 'Add Package'])
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white sm:hidden">Packages</h1>
    @endinclude
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Services</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-500">Actions</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                    @forelse ($packages as $package)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="whitespace-nowrap px-6 py-4 font-medium">{{ $package->name }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ number_format($package->price, 2) }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-slate-500">{{ $package->services?->count() ?? 0 }} services</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <a href="{{ route('packages.show', $package) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">View</a>
                                <span class="mx-2 text-slate-300">|</span>
                                <a href="{{ route('packages.edit', $package) }}" class="text-slate-600 hover:text-slate-900 dark:text-slate-400">Edit</a>
                            </td>
                        </tr>
                    @empty @endforelse
                </tbody>
            </table>
        </div>
        @if ($packages->isEmpty())<x-ui.empty-state title="No packages yet" description="Bundle services into packages." />@endif
        @include('partials.crud.pagination', ['paginator' => $packages])
    </x-ui.card>
</x-layouts.app>""")

write("packages/create.blade.php", create_page("Add Package", "packages.store", "packages.index", "packages._form", "Create Package"))
write("packages/edit.blade.php", edit_page("Edit Package", "packages.update", "packages.show", "packages._form", "package", "Save Changes"))
write("packages/show.blade.php", show_page("{{ $package->name }}", "packages.index", "packages.edit", "packages.destroy", "Delete this package?", """    <div class="grid gap-6 lg:grid-cols-2">
        <x-ui.card>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Price</dt><dd class="font-medium">{{ number_format($package->price, 2) }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Duration</dt><dd>{{ $package->duration_minutes ?? '—' }} min</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd>@if($package->is_active)<x-ui.badge color="green">Active</x-ui.badge>@else<x-ui.badge color="slate">Inactive</x-ui.badge>@endif</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Description</dt><dd>{{ $package->description ?? '—' }}</dd></div>
            </dl>
        </x-ui.card>
        <x-ui.card>
            <h2 class="mb-4 text-lg font-semibold">Included Services</h2>
            <div class="flex flex-wrap gap-2">
                @forelse ($package->services ?? [] as $service)
                    <x-ui.badge color="indigo">{{ $service->name }}</x-ui.badge>
                @empty
                    <p class="text-sm text-slate-500">No services included.</p>
                @endforelse
            </div>
        </x-ui.card>
    </div>"""))

# ============ BOOKINGS ============
write("bookings/_form.blade.php", f"""@php $booking = $booking ?? null; @endphp
<div class="grid gap-6 sm:grid-cols-2">
    <div>
        <x-input-label for="customer_id" value="Customer" />
        <select id="customer_id" name="customer_id" class="{IC}" required>
            <option value="">Select customer…</option>
            @foreach ($customers as $customer)
                <option value="{{{{ $customer->id }}}}" @selected(old('customer_id', $booking->customer_id ?? '') == $customer->id)>{{{{ $customer->full_name }}}}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('customer_id')" />
    </div>
    <div>
        <x-input-label for="vehicle_id" value="Vehicle" />
        <select id="vehicle_id" name="vehicle_id" class="{IC}">
            <option value="">Select vehicle…</option>
            @foreach ($vehicles as $vehicle)
                <option value="{{{{ $vehicle->id }}}}" @selected(old('vehicle_id', $booking->vehicle_id ?? '') == $vehicle->id)>{{{{ $vehicle->registration_number }}}}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('vehicle_id')" />
    </div>
    <div>
        <x-input-label for="type" value="Type" />
        <select id="type" name="type" class="{IC}" required>
            @foreach (['appointment', 'walk_in'] as $type)
                <option value="{{{{ $type }}}}" @selected(old('type', $booking->type ?? 'appointment') == $type)>{{{{ ucfirst(str_replace('_', ' ', $type)) }}}}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('type')" />
    </div>
    <div>
        <x-input-label for="status" value="Status" />
        <select id="status" name="status" class="{IC}" required>
            @foreach (['pending', 'confirmed', 'in_progress', 'completed', 'cancelled'] as $status)
                <option value="{{{{ $status }}}}" @selected(old('status', $booking->status ?? 'pending') == $status)>{{{{ ucfirst(str_replace('_', ' ', $status)) }}}}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('status')" />
    </div>
    <div>
        <x-input-label for="scheduled_at" value="Scheduled At" />
        <x-text-input id="scheduled_at" name="scheduled_at" type="datetime-local" class="{IC}" :value="old('scheduled_at', isset($booking->scheduled_at) ? $booking->scheduled_at->format('Y-m-d\\TH:i') : '')" />
        <x-input-error :messages="$errors->get('scheduled_at')" />
    </div>
    <div>
        <x-input-label for="ends_at" value="Ends At" />
        <x-text-input id="ends_at" name="ends_at" type="datetime-local" class="{IC}" :value="old('ends_at', isset($booking->ends_at) ? $booking->ends_at->format('Y-m-d\\TH:i') : '')" />
        <x-input-error :messages="$errors->get('ends_at')" />
    </div>
    <div class="sm:col-span-2">
        <x-input-label value="Services" />
        <div class="mt-2 flex flex-wrap gap-3">
            @foreach ($services as $service)
                <label class="flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 dark:border-slate-700">
                    <input type="checkbox" name="services[]" value="{{{{ $service->id }}}}" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600" @checked(in_array($service->id, old('services', $booking?->services->pluck('id')->toArray() ?? [])))>
                    <span class="text-sm">{{{{ $service->name }}}}</span>
                </label>
            @endforeach
        </div>
        <x-input-error :messages="$errors->get('services')" />
    </div>
    <div class="sm:col-span-2">
        <x-input-label for="notes" value="Notes" />
        <textarea id="notes" name="notes" rows="3" class="{IC}">{{ old('notes', $booking->notes ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('notes')" />
    </div>
</div>""")

BOOKING_ROW = """                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="whitespace-nowrap px-6 py-4 font-medium">{{ $booking->customer?->full_name ?? '—' }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ $booking->vehicle?->registration_number ?? '—' }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ $booking->scheduled_at?->format('M j, Y g:i A') ?? '—' }}</td>
                            <td class="whitespace-nowrap px-6 py-4"><x-ui.badge color="indigo">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</x-ui.badge></td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <a href="{{ route('bookings.show', $booking) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">View</a>
                                <span class="mx-2 text-slate-300">|</span>
                                <a href="{{ route('bookings.edit', $booking) }}" class="text-slate-600 hover:text-slate-900 dark:text-slate-400">Edit</a>
                            </td>
                        </tr>"""

def booking_index(title, empty_title, empty_desc):
    return f"""<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">{title}</h1></x-slot>
    @include('partials.crud.index-header', ['createRoute' => route('bookings.create'), 'createLabel' => 'New Booking'])
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white sm:hidden">{title}</h1>
    @endinclude
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Vehicle</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Scheduled</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-500">Actions</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                    @forelse ($bookings as $booking)
{BOOKING_ROW}
                    @empty @endforelse
                </tbody>
            </table>
        </div>
        @if ($bookings->isEmpty())<x-ui.empty-state title="{empty_title}" description="{empty_desc}" />@endif
        @include('partials.crud.pagination', ['paginator' => $bookings])
    </x-ui.card>
</x-layouts.app>"""

write("bookings/index.blade.php", booking_index("Bookings", "No bookings yet", "Create a booking to schedule services."))
write("bookings/walk-ins.blade.php", booking_index("Walk-In Bookings", "No walk-ins", "Walk-in bookings will appear here."))
write("bookings/pending.blade.php", booking_index("Pending Bookings", "No pending bookings", "Pending bookings will appear here."))
write("bookings/completed.blade.php", booking_index("Completed Bookings", "No completed bookings", "Completed bookings will appear here."))
write("bookings/cancelled.blade.php", booking_index("Cancelled Bookings", "No cancelled bookings", "Cancelled bookings will appear here."))
write("bookings/create.blade.php", create_page("New Booking", "bookings.store", "bookings.index", "bookings._form", "Create Booking"))
write("bookings/edit.blade.php", edit_page("Edit Booking", "bookings.update", "bookings.show", "bookings._form", "booking", "Save Changes"))
write("bookings/show.blade.php", show_page("Booking #{{ $booking->id }}", "bookings.index", "bookings.edit", "bookings.destroy", "Delete this booking?", """    <div class="grid gap-6 lg:grid-cols-2">
        <x-ui.card>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Customer</dt><dd class="font-medium">{{ $booking->customer?->full_name ?? '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Vehicle</dt><dd>{{ $booking->vehicle?->registration_number ?? '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Type</dt><dd>{{ ucfirst(str_replace('_', ' ', $booking->type)) }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd><x-ui.badge color="indigo">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</x-ui.badge></dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Scheduled</dt><dd>{{ $booking->scheduled_at?->format('M j, Y g:i A') ?? '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Ends</dt><dd>{{ $booking->ends_at?->format('M j, Y g:i A') ?? '—' }}</dd></div>
            </dl>
        </x-ui.card>
        <x-ui.card>
            <h2 class="mb-4 text-lg font-semibold">Services</h2>
            <div class="flex flex-wrap gap-2">
                @forelse ($booking->services ?? [] as $service)
                    <x-ui.badge color="indigo">{{ $service->name }}</x-ui.badge>
                @empty
                    <p class="text-sm text-slate-500">No services selected.</p>
                @endforelse
            </div>
            @if($booking->notes)<p class="mt-4 text-sm text-slate-500">{{ $booking->notes }}</p>@endif
        </x-ui.card>
    </div>"""))
write("bookings/calendar.blade.php", layout("Booking Calendar", """    <x-ui.card>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($bookings as $booking)
                <a href="{{ route('bookings.show', $booking) }}" class="rounded-xl border border-slate-200 p-4 transition hover:border-indigo-300 hover:shadow-sm dark:border-slate-700">
                    <div class="mb-2 flex items-center justify-between">
                        <span class="font-medium">{{ $booking->customer?->full_name ?? 'Walk-in' }}</span>
                        <x-ui.badge color="indigo">{{ $booking->status }}</x-ui.badge>
                    </div>
                    <p class="text-sm text-slate-500">{{ $booking->scheduled_at?->format('M j, Y g:i A') }}</p>
                    <p class="text-sm text-slate-400">{{ $booking->vehicle?->registration_number ?? 'No vehicle' }}</p>
                </a>
            @empty
                <div class="col-span-full"><x-ui.empty-state title="No bookings scheduled" description="Bookings will appear on the calendar." /></div>
            @endforelse
        </div>
    </x-ui.card>"""))

print(f"Part 2b done, total: {len(created)}")
