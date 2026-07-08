<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\FixedAsset;
use Illuminate\View\View;

class MobileFixedAssetController extends Controller
{
    public function index(): View
    {
        return view('mobile.fixed-assets.index', [
            'assets' => FixedAsset::query()
                ->with(['supplier', 'assignee'])
                ->latest()
                ->paginate(20),
        ]);
    }
}
