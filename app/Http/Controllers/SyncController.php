<?php

namespace App\Http\Controllers;

use App\Http\Requests\SyncPushRequest;
use App\Services\BranchService;
use App\Services\Sync\SyncService;
use Illuminate\Http\JsonResponse;

class SyncController extends Controller
{
    public function __construct(
        protected SyncService $syncService,
        protected BranchService $branchService,
    ) {}

    public function bootstrap(): JsonResponse
    {
        $branchId = $this->branchService->currentBranchId();

        if ($branchId === null) {
            return response()->json(['message' => 'Select a branch before syncing.'], 422);
        }

        return response()->json(
            $this->syncService->bootstrap($branchId)
        );
    }

    public function push(SyncPushRequest $request): JsonResponse
    {
        $branchId = $this->branchService->currentBranchId();

        if ($branchId === null) {
            return response()->json(['message' => 'Select a branch before syncing.'], 422);
        }

        return response()->json([
            'results' => $this->syncService->push(
                $request->user(),
                $branchId,
                $request->validated('mutations'),
            ),
        ]);
    }
}
