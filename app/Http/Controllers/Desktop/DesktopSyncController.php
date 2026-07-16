<?php

namespace App\Http\Controllers\Desktop;

use App\Http\Controllers\Controller;
use App\Http\Requests\SyncPushRequest;
use App\Services\BranchService;
use App\Services\Sync\SyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DesktopSyncController extends Controller
{
    public function __construct(
        protected SyncService $syncService,
        protected BranchService $branchService,
    ) {}

    public function ping(Request $request): JsonResponse
    {
        $branchId = $this->branchService->currentBranchId();

        if ($branchId === null) {
            return response()->json(['message' => 'Select a branch before syncing.'], 422);
        }

        return response()->json([
            'ok' => true,
            'user_id' => $request->user()?->id,
            'branch_id' => $branchId,
        ]);
    }

    public function bootstrap(Request $request): JsonResponse
    {
        $branchId = $this->branchService->currentBranchId();

        if ($branchId === null) {
            return response()->json(['message' => 'Select a branch before syncing.'], 422);
        }

        return response()->json(
            $this->syncService->bootstrap($branchId, $request->user())
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
