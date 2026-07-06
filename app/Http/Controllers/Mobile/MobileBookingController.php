<?php

namespace App\Http\Controllers\Mobile;

use App\Enums\BookingStatus;
use App\Enums\BookingType;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MobileBookingController extends Controller
{
    public function index(Request $request): View
    {
        $query = Booking::query()->with(['customer', 'vehicle'])->latest('scheduled_at');

        if ($request->filled('date')) {
            $query->whereDate('scheduled_at', $request->date('date'));
        }

        if ($request->filled('status')) {
            $status = BookingStatus::tryFrom($request->string('status')->toString());

            if ($status) {
                $query->where('status', $status);
            }
        }

        return view('mobile.bookings.index', [
            'bookings' => $query->paginate(20)->withQueryString(),
            'filters' => [
                'date' => $request->input('date'),
                'status' => $request->input('status'),
            ],
            'statuses' => BookingStatus::cases(),
        ]);
    }

    public function show(Booking $booking): View
    {
        return view('mobile.bookings.show', [
            'booking' => $booking->load(['customer', 'vehicle', 'services', 'jobCard']),
        ]);
    }

    public function calendar(Request $request): View
    {
        $date = $request->filled('date')
            ? Carbon::parse($request->input('date'))
            : now();

        $start = $date->copy()->startOfWeek();
        $end = $date->copy()->endOfWeek();

        $bookings = Booking::query()
            ->with(['customer', 'vehicle'])
            ->whereBetween('scheduled_at', [$start, $end])
            ->orderBy('scheduled_at')
            ->get()
            ->groupBy(fn (Booking $booking) => $booking->scheduled_at->toDateString());

        return view('mobile.bookings.calendar', [
            'date' => $date,
            'start' => $start,
            'bookings' => $bookings,
            'stats' => [
                'total' => Booking::query()->whereDate('scheduled_at', $date)->count(),
                'pending' => Booking::query()->whereDate('scheduled_at', $date)->where('status', BookingStatus::Pending)->count(),
                'confirmed' => Booking::query()->whereDate('scheduled_at', $date)->where('status', BookingStatus::Confirmed)->count(),
            ],
        ]);
    }

    public function walkIns(): View
    {
        $bookings = Booking::query()
            ->with(['customer', 'vehicle'])
            ->where('type', BookingType::WalkIn)
            ->latest('scheduled_at')
            ->paginate(20);

        return view('mobile.bookings.walk-ins', [
            'bookings' => $bookings,
        ]);
    }
}
