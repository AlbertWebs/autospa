<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AssignsBranchId;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ServiceController extends Controller
{
    use AssignsBranchId;

    public function index(): View
    {
        return view('services.index', [
            'services' => Service::query()->with('category')->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('services.create', [
            'categories' => ServiceCategory::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(StoreServiceRequest $request): RedirectResponse
    {
        $service = Service::create($this->withBranchId($request->validated()));

        return redirect()->route('services.show', $service)
            ->with('success', 'Service created.');
    }

    public function show(Service $service): View
    {
        return view('services.show', ['service' => $service->load('category')]);
    }

    public function edit(Service $service): View
    {
        return view('services.edit', [
            'service' => $service,
            'categories' => ServiceCategory::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateServiceRequest $request, Service $service): RedirectResponse
    {
        $service->update($request->validated());

        return redirect()->route('services.show', $service)
            ->with('success', 'Service updated.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $service->delete();

        return redirect()->route('services.index')
            ->with('success', 'Service deleted.');
    }
}
