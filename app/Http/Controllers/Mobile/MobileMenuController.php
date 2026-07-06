<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Support\MobileNavigation;
use Illuminate\View\View;

class MobileMenuController extends Controller
{
    public function __construct(
        protected MobileNavigation $mobileNavigation,
    ) {}

    public function index(): View
    {
        return view('mobile.menu', [
            'sections' => $this->mobileNavigation->sections(),
        ]);
    }
}
