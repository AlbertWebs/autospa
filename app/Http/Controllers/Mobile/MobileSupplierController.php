<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\View\View;

class MobileSupplierController extends Controller
{
    public function index(): View
    {
        return view('mobile.suppliers.index', [
            'suppliers' => Supplier::query()->latest()->paginate(20),
        ]);
    }
}
