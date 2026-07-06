<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendIntegrationTestRequest;
use App\Models\Setting;
use App\Services\IntegrationTestGroundService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TestGroundController extends Controller
{
    public function __construct(
        protected IntegrationTestGroundService $testGroundService,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', Setting::class);

        return view('settings.test-ground.index', [
            'status' => $this->testGroundService->status(),
        ]);
    }

    public function send(SendIntegrationTestRequest $request): RedirectResponse
    {
        $channel = $request->validated('channel');

        $result = match ($channel) {
            'email' => $this->testGroundService->sendEmail(
                $request->validated('recipient'),
                $request->validated('message'),
                $request->validated('subject'),
            ),
            'sms' => $this->testGroundService->sendSms(
                $request->validated('recipient'),
                $request->validated('message'),
            ),
            'whatsapp' => $this->testGroundService->sendWhatsApp(
                $request->validated('recipient'),
                $request->validated('message'),
            ),
            'mpesa' => $this->testGroundService->sendMpesaStk(
                $request->validated('recipient'),
                (float) $request->validated('amount'),
            ),
        };

        return back()
            ->with($result['success'] ? 'success' : 'error', $result['message'])
            ->with('test_result', $result);
    }
}
