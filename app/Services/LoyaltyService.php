<?php

namespace App\Services;

use App\Enums\JobCardStatus;
use App\Models\Vehicle;
use App\Support\LoyaltySettings;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class LoyaltyService
{
    public function paginatedVehicleWashes(Request $request, int $perPage = 25): LengthAwarePaginator
    {
        $washesBeforeFree = LoyaltySettings::washesBeforeFree();

        $query = Vehicle::query()
            ->with('customer')
            ->withCount(['jobCards as wash_count' => fn ($builder) => $builder->where('status', JobCardStatus::Completed)])
            ->whereHas('jobCards', fn ($builder) => $builder->where('status', JobCardStatus::Completed));

        if ($request->filled('q')) {
            $search = $request->string('q')->toString();
            $query->where(function ($builder) use ($search) {
                $builder->where('registration_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($customer) => $customer->where('full_name', 'like', "%{$search}%"));
            });
        }

        return $query
            ->orderByDesc('wash_count')
            ->orderBy('registration_number')
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (Vehicle $vehicle) => $this->vehicleWashSummary($vehicle, $washesBeforeFree));
    }

    /**
     * @return array{
     *     vehicle: Vehicle,
     *     wash_count: int,
     *     paid_in_cycle: int,
     *     washes_until_free: int,
     *     progress_percent: int,
     *     status: string,
     *     status_label: string,
     *     color: string,
     * }
     */
    public function vehicleWashSummary(Vehicle $vehicle, ?int $washesBeforeFree = null): array
    {
        $washCount = (int) ($vehicle->wash_count ?? $vehicle->jobCards()->where('status', JobCardStatus::Completed)->count());

        return array_merge(
            ['vehicle' => $vehicle, 'wash_count' => $washCount],
            $this->progressForWashCount($washCount, $washesBeforeFree ?? LoyaltySettings::washesBeforeFree()),
        );
    }

    /**
     * @return array{
     *     paid_in_cycle: int,
     *     washes_until_free: int,
     *     progress_percent: int,
     *     status: string,
     *     status_label: string,
     *     color: string,
     * }
     */
    public function progressForWashCount(int $washCount, int $washesBeforeFree): array
    {
        $cycleSize = $washesBeforeFree + 1;
        $remainder = $washCount % $cycleSize;

        if ($remainder === $washesBeforeFree) {
            return [
                'paid_in_cycle' => $washesBeforeFree,
                'washes_until_free' => 0,
                'progress_percent' => 100,
                'status' => 'free_next',
                'status_label' => 'Free wash next',
                'color' => 'emerald',
            ];
        }

        if ($remainder === 0 && $washCount > 0) {
            return [
                'paid_in_cycle' => 0,
                'washes_until_free' => $washesBeforeFree,
                'progress_percent' => 0,
                'status' => 'cycle_reset',
                'status_label' => 'New cycle',
                'color' => 'slate',
            ];
        }

        $paidInCycle = $remainder;
        $washesUntilFree = $washesBeforeFree - $remainder;
        $progressPercent = (int) round(($paidInCycle / $washesBeforeFree) * 100);

        if ($remainder >= $washesBeforeFree - 2) {
            return [
                'paid_in_cycle' => $paidInCycle,
                'washes_until_free' => $washesUntilFree,
                'progress_percent' => $progressPercent,
                'status' => 'almost_free',
                'status_label' => "{$paidInCycle}/{$washesBeforeFree} washes",
                'color' => 'sky',
            ];
        }

        return [
            'paid_in_cycle' => $paidInCycle,
            'washes_until_free' => $washesUntilFree,
            'progress_percent' => $progressPercent,
            'status' => 'in_progress',
            'status_label' => "{$paidInCycle}/{$washesBeforeFree} washes",
            'color' => 'amber',
        ];
    }
}
