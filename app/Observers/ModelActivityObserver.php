<?php

namespace App\Observers;

use App\Services\ActivityLogService;
use Illuminate\Database\Eloquent\Model;

class ModelActivityObserver
{
    public function __construct(
        protected ActivityLogService $activityLogService,
    ) {}

    public function created(Model $model): void
    {
        $this->activityLogService->fromModel($model, 'created');
    }

    public function updated(Model $model): void
    {
        $changes = collect($model->getChanges())
            ->except(config('activity_log.ignored_change_keys', []))
            ->keys();

        if ($changes->isEmpty()) {
            return;
        }

        $this->activityLogService->fromModel($model, 'updated');
    }

    public function deleted(Model $model): void
    {
        $this->activityLogService->fromModel($model, 'deleted');
    }

    public function restored(Model $model): void
    {
        $this->activityLogService->fromModel($model, 'restored');
    }
}
