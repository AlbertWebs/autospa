<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ManualController extends Controller
{
    public function index(): View
    {
        return view('manual.index', [
            'sections' => config('manual.sections', []),
        ]);
    }
}
