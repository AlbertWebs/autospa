<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class PinController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $hasExistingPin = filled($user->pin);

        $rules = [
            'pin' => ['required', 'digits_between:4,6', 'confirmed'],
        ];

        if ($hasExistingPin) {
            $rules['current_pin'] = ['required', 'digits_between:4,6'];
        }

        $validated = $request->validateWithBag('updatePin', $rules);

        if ($hasExistingPin && ! Hash::check($validated['current_pin'], $user->pin)) {
            throw ValidationException::withMessages([
                'current_pin' => __('The provided PIN does not match your current PIN.'),
            ])->errorBag('updatePin');
        }

        $user->update([
            'pin' => $validated['pin'],
        ]);

        return back()->with('status', 'pin-updated');
    }
}
