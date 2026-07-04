<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    public function complete(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->onboarding_completed_at === null) {
            $user->forceFill(['onboarding_completed_at' => now()])->save();
        }

        return response()->json([
            'message' => 'Welcome tour completed.',
        ]);
    }
}
