<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        return view('notifications.index', [
            'notifications' => $request->user()->notifications()->paginate(20),
        ]);
    }

    public function markAsRead(Request $request, string $id): RedirectResponse
    {
        $request->user()->notifications()->where('id', $id)->first()?->markAsRead();

        return back();
    }
}
