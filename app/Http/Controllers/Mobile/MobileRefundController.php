<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Refund;
use Illuminate\View\View;

class MobileRefundController extends Controller
{
    public function index(): View
    {
        return view('mobile.refunds.index', [
            'refunds' => Refund::query()->with('invoice')->latest()->paginate(20),
        ]);
    }
}
