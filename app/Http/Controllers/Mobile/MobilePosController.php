<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\JobCard;
use App\Services\BranchService;
use App\Services\PosService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MobilePosController extends Controller
{
    public function __construct(
        protected PosService $posService,
        protected BranchService $branchService,
    ) {}

    public function index(Request $request): View
    {
        $branchId = $this->branchService->currentBranchId();
        $jobCard = null;

        if ($request->filled('job_card') && $branchId !== null) {
            $jobCard = JobCard::query()
                ->where('branch_id', $branchId)
                ->with(['services.service', 'products.product'])
                ->find($request->integer('job_card'));
        }

        return view('mobile.pos.index', $this->posService->checkoutData($branchId, $jobCard));
    }
}
