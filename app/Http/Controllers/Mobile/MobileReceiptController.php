<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Receipt;
use Illuminate\View\View;

class MobileReceiptController extends Controller
{
    public function index(): View
    {
        return view('mobile.receipts.index', [
            'receipts' => Receipt::query()->with('invoice')->latest()->paginate(20),
        ]);
    }
}
