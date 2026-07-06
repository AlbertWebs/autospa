<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Services\PosService;
use Illuminate\View\View;

class MobilePosController extends Controller
{
    public function __construct(
        protected PosService $posService,
    ) {}

    public function index(): View
    {
        return view('mobile.pos.index', $this->posService->checkoutData());
    }
}
