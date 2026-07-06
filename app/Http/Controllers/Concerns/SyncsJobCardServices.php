<?php

namespace App\Http\Controllers\Concerns;

use App\Models\JobCard;
use App\Models\Service;

trait SyncsJobCardServices
{
    /**
     * @param  array<int, int|string>  $serviceIds
     */
    protected function syncJobCardServices(JobCard $jobCard, array $serviceIds): void
    {
        $serviceIds = array_values(array_unique(array_map('intval', $serviceIds)));

        $services = Service::query()
            ->where('branch_id', $jobCard->branch_id)
            ->where('is_active', true)
            ->whereIn('id', $serviceIds)
            ->get()
            ->keyBy('id');

        $jobCard->services()->delete();

        foreach ($serviceIds as $serviceId) {
            $service = $services->get($serviceId);

            if (! $service) {
                continue;
            }

            $jobCard->services()->create([
                'service_id' => $service->id,
                'price' => $service->price,
                'status' => 'pending',
            ]);
        }
    }

    protected function availableServices()
    {
        return Service::query()
            ->where('branch_id', session('current_branch_id'))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }
}
