<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AssignsBranchId;
use App\Http\Requests\StorePackageRequest;
use App\Http\Requests\UpdatePackageRequest;
use App\Models\Package;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PackageController extends Controller
{
    use AssignsBranchId;

    public function index(): View
    {
        return view('packages.index', [
            'packages' => Package::query()->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('packages.create', [
            'services' => Service::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(StorePackageRequest $request): RedirectResponse
    {
        $package = Package::create($this->withBranchId($request->safe()->except('services')));
        $package->services()->sync($request->validated('services', []));

        return redirect()->route('packages.show', $package)
            ->with('success', 'Package created.');
    }

    public function show(Package $package): View
    {
        return view('packages.show', ['package' => $package->load('services')]);
    }

    public function edit(Package $package): View
    {
        return view('packages.edit', [
            'package' => $package->load('services'),
            'services' => Service::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(UpdatePackageRequest $request, Package $package): RedirectResponse
    {
        $package->update($request->safe()->except('services'));
        $package->services()->sync($request->validated('services', []));

        return redirect()->route('packages.show', $package)
            ->with('success', 'Package updated.');
    }

    public function destroy(Package $package): RedirectResponse
    {
        $package->delete();

        return redirect()->route('packages.index')
            ->with('success', 'Package deleted.');
    }
}
