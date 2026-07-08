<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\View\View;

class MobileServiceController extends Controller
{
    public function index(): View
    {
        return view('mobile.services.index', [
            'services' => Service::query()->with('category')->orderBy('name')->paginate(20),
        ]);
    }
    public function categories(): View
    {
        return view('mobile.services.categories', [
            'categories' => ServiceCategory::query()->orderBy('name')->paginate(20),
        ]);
    }
}
