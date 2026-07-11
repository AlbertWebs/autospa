<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurgeTestDataRequest;
use App\Services\TestDataPurgeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DangerZoneController extends Controller
{
    public function __construct(
        protected TestDataPurgeService $testDataPurgeService,
    ) {}

    public function show(Request $request): View
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        return view('danger-zone.show', [
            'groups' => $this->testDataPurgeService->catalog(),
        ]);
    }

    public function destroy(PurgeTestDataRequest $request): RedirectResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        $result = $this->testDataPurgeService->purge($request->validated('groups'));
        $total = array_sum($result['deleted']);

        return redirect()
            ->route('danger-zone.show')
            ->with('status', $total > 0
                ? "Deleted {$total} test data row(s) across ".count($result['groups']).' group(s).'
                : 'No matching test data rows were found to delete.');
    }
}
