<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MpesaTransaction;
use App\Services\MpesaLifecycleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MpesaCallbackController extends Controller
{
    public function stkResult(Request $request, MpesaLifecycleService $lifecycle): JsonResponse
    {
        $lifecycle->handleStkResult($request->all());

        return $this->accepted();
    }

    public function result(Request $request, MpesaLifecycleService $lifecycle): JsonResponse
    {
        $lifecycle->handleResult($request->all());

        return $this->accepted();
    }

    public function timeout(Request $request, MpesaLifecycleService $lifecycle): JsonResponse
    {
        $lifecycle->handleTimeout($this->resolveTimeoutFlow($request->all()), $request->all());

        return $this->accepted();
    }

    public function balanceResult(Request $request, MpesaLifecycleService $lifecycle): JsonResponse
    {
        $lifecycle->handleBalanceResult($request->all());

        return $this->accepted();
    }

    public function balanceTimeout(Request $request, MpesaLifecycleService $lifecycle): JsonResponse
    {
        $lifecycle->handleTimeout('balance', $request->all());

        return $this->accepted();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function resolveTimeoutFlow(array $payload): string
    {
        $conversationId = (string) ($payload['ConversationID'] ?? '');
        $originatorConversationId = (string) ($payload['OriginatorConversationID'] ?? '');

        $outbound = MpesaTransaction::query()
            ->where('direction', 'outbound')
            ->where(function ($query) use ($conversationId, $originatorConversationId) {
                if ($conversationId !== '') {
                    $query->orWhere('conversation_id', $conversationId)
                        ->orWhere('reference', $conversationId);
                }

                if ($originatorConversationId !== '') {
                    $query->orWhere('originator_conversation_id', $originatorConversationId)
                        ->orWhere('reference', $originatorConversationId);
                }
            })
            ->latest('id')
            ->first();

        return $outbound?->flow ?? 'b2c';
    }

    protected function accepted(): JsonResponse
    {
        return response()->json([
            'ResultCode' => 0,
            'ResultDesc' => 'Accepted',
        ]);
    }
}
