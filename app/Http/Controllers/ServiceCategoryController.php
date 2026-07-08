<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AssignsBranchId;
use App\Http\Requests\StoreServiceCategoryRequest;
use App\Http\Requests\UpdateServiceCategoryRequest;
use App\Models\ServiceCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ServiceCategoryController extends Controller
{
    use AssignsBranchId;

    public function index(): View
    {
        return view('service-categories.index', [
            'categories' => ServiceCategory::query()->orderBy('sort_order')->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('service-categories.create');
    }

    public function store(StoreServiceCategoryRequest $request): RedirectResponse
    {
        $category = ServiceCategory::create($this->withBranchId($request->validated()));

        return redirect()->route('services.categories.index')
            ->with('success', 'Category created.');
    }

    public function show(ServiceCategory $category): View
    {
        return view('service-categories.show', [
            'category' => $category->load('services'),
        ]);
    }

    public function edit(ServiceCategory $category): View
    {
        return view('service-categories.edit', ['category' => $category]);
    }

    public function update(UpdateServiceCategoryRequest $request, ServiceCategory $category): RedirectResponse
    {
        $category->update($request->validated());

        return redirect()->route('services.categories.index')
            ->with('success', 'Category updated.');
    }

    public function destroy(ServiceCategory $category): RedirectResponse
    {
        $category->delete();

        return redirect()->route('services.categories.index')
            ->with('success', 'Category deleted.');
    }
}
