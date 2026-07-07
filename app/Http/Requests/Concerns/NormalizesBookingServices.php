<?php

namespace App\Http\Requests\Concerns;

use App\Models\Service;

trait NormalizesBookingServices
{
    protected function prepareForValidation(): void
    {
        $services = $this->input('services');

        if (! is_array($services) || $services === []) {
            return;
        }

        if (isset($services[0]['service_id'])) {
            return;
        }

        $serviceIds = collect($services)
            ->filter(fn ($id) => filled($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($serviceIds->isEmpty()) {
            $this->merge(['services' => []]);

            return;
        }

        $catalog = Service::query()
            ->whereIn('id', $serviceIds)
            ->get()
            ->keyBy('id');

        $this->merge([
            'services' => $serviceIds->map(function (int $id) use ($catalog) {
                $service = $catalog->get($id);

                return [
                    'service_id' => $id,
                    'price' => $service?->price ?? 0,
                    'duration_minutes' => $service?->duration_minutes,
                ];
            })->values()->all(),
        ]);
    }
}
