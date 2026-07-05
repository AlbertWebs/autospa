<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AssignsBranchId;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Enums\BookingStatus;
use App\Enums\BookingType;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BookingController extends Controller
{
    use AssignsBranchId;

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

        if ($request->filled('type')) {
            $type = BookingType::tryFrom($request->string('type')->toString());

            if ($type) {
                $query->where('type', $type);
            }
        }

        return view('bookings.index', [
            'bookings' => $query->paginate(15)->withQueryString(),
            'filters' => [
                'date' => $request->input('date'),
                'status' => $request->input('status'),
                'type' => $request->input('type'),
            ],
        ]);
    }

    public function create(Request $request): View
    {
        $scheduledAt = null;

        if ($request->filled('scheduled_at')) {
            try {
                $scheduledAt = Carbon::parse($request->query('scheduled_at'))->format('Y-m-d\TH:i');
            } catch (\Throwable) {
                $scheduledAt = null;
            }
        }

        return view('bookings.create', [
            'customers' => Customer::query()->orderBy('full_name')->get(),
            'vehicles' => Vehicle::query()->with('customer')->get(),
            'services' => Service::query()->where('is_active', true)->get(),
            'scheduledAt' => $scheduledAt,
        ]);
    }

    public function store(StoreBookingRequest $request): RedirectResponse
    {
        $booking = Booking::create([
            ...$this->withBranchId($request->safe()->except('services')),
            'created_by' => $request->user()->id,
        ]);

        if ($services = $request->validated('services')) {
            $booking->bookingServices()->createMany($services);
        }

        return redirect()->route('bookings.show', $booking)
            ->with('success', 'Booking created.');
    }

    public function show(Booking $booking): View
    {
        return view('bookings.show', [
            'booking' => $booking->load(['customer', 'vehicle', 'bookingServices.service', 'jobCard', 'creator']),
        ]);
    }

    public function edit(Booking $booking): View
    {
        return view('bookings.edit', [
            'booking' => $booking->load('bookingServices'),
            'customers' => Customer::query()->orderBy('full_name')->get(),
            'vehicles' => Vehicle::query()->with('customer')->get(),
            'services' => Service::query()->where('is_active', true)->get(),
        ]);
    }

    public function update(UpdateBookingRequest $request, Booking $booking): RedirectResponse
    {
        $booking->update($request->safe()->except('services'));

        if ($request->has('services')) {
            $booking->bookingServices()->delete();
            $booking->bookingServices()->createMany($request->validated('services', []));
        }

        return redirect()->route('bookings.show', $booking)
            ->with('success', 'Booking updated.');
    }

    public function destroy(Booking $booking): RedirectResponse
    {
        $booking->delete();

        return redirect()->route('bookings.index')
            ->with('success', 'Booking deleted.');
    }

    public function calendar(Request $request): View
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $month = max(1, min(12, $month));

        $date = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $gridStart = $date->copy()->startOfWeek(Carbon::SUNDAY);
        $gridEnd = $date->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $bookings = Booking::query()
            ->with(['customer', 'vehicle'])
            ->whereBetween('scheduled_at', [$gridStart, $gridEnd])
            ->orderBy('scheduled_at')
            ->get()
            ->groupBy(fn (Booking $booking) => $booking->scheduled_at->format('Y-m-d'));

        $weeks = [];
        $cursor = $gridStart->copy();
        while ($cursor <= $gridEnd) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $week[] = $cursor->copy();
                $cursor->addDay();
            }
            $weeks[] = $week;
        }

        $monthBookings = Booking::query()->whereYear('scheduled_at', $year)->whereMonth('scheduled_at', $month);

        $stats = [
            'total' => (clone $monthBookings)->count(),
            'pending' => (clone $monthBookings)->where('status', BookingStatus::Pending)->count(),
            'confirmed' => (clone $monthBookings)->where('status', BookingStatus::Confirmed)->count(),
            'in_progress' => (clone $monthBookings)->where('status', BookingStatus::InProgress)->count(),
            'today' => Booking::query()->whereDate('scheduled_at', today())->count(),
        ];

        return view('bookings.calendar', [
            'date' => $date,
            'weeks' => $weeks,
            'bookings' => $bookings,
            'stats' => $stats,
            'prevMonth' => $date->copy()->subMonth(),
            'nextMonth' => $date->copy()->addMonth(),
        ]);
    }

    public function walkIns(): RedirectResponse
    {
        return redirect()->route('bookings.index', ['type' => BookingType::WalkIn->value]);
    }

    public function pending(): RedirectResponse
    {
        return redirect()->route('bookings.index', ['status' => BookingStatus::Pending->value]);
    }

    public function completed(): RedirectResponse
    {
        return redirect()->route('bookings.index', ['status' => BookingStatus::Completed->value]);
    }

    public function cancelled(): RedirectResponse
    {
        return redirect()->route('bookings.index', ['status' => BookingStatus::Cancelled->value]);
    }
}
