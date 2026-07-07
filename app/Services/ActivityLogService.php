<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\Commission;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\JobCard;
use App\Models\Setting;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ActivityLogService
{
    protected static bool $suppress = false;

    protected static bool $forceFailForTesting = false;

    public static function suppress(bool $suppress = true): void
    {
        static::$suppress = $suppress;
    }

    public static function forceFailForTesting(bool $forceFail = true): void
    {
        static::$forceFailForTesting = $forceFail;
    }

    public function record(
        string $event,
        ?string $description = null,
        ?Model $subject = null,
        array $properties = [],
        ?int $userId = null,
        ?int $branchId = null,
    ): ?ActivityLog {
        if (static::$suppress || ! config('activity_log.enabled', true)) {
            return null;
        }

        $write = fn (): ?ActivityLog => $this->persist(
            $event,
            $description,
            $subject,
            $properties,
            $userId,
            $branchId,
        );

        if (DB::transactionLevel() > 0) {
            DB::afterCommit($write);

            return null;
        }

        return $write();
    }

    public function fromModel(Model $model, string $action, array $extra = []): void
    {
        $this->record(
            $this->modelEventKey($model, $action),
            $this->describeModel($model, $action),
            $model,
            array_merge($this->modelProperties($model, $action), $extra),
            Auth::id(),
            $this->resolveBranchId($model),
        );
    }

    /**
     * @param  array<string, mixed>  $properties
     */
    protected function persist(
        string $event,
        ?string $description,
        ?Model $subject,
        array $properties,
        ?int $userId,
        ?int $branchId,
    ): ?ActivityLog {
        try {
            if (static::$forceFailForTesting) {
                throw new \RuntimeException('Activity log write suppressed for testing.');
            }

            return ActivityLog::query()->create([
                'user_id' => $userId ?? Auth::id(),
                'branch_id' => $branchId,
                'event' => $event,
                'subject_type' => $subject?->getMorphClass(),
                'subject_id' => $subject?->getKey(),
                'properties' => $this->redactProperties($properties),
                'description' => $description,
            ]);
        } catch (Throwable $exception) {
            Log::warning('Activity log write failed.', [
                'event' => $event,
                'message' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    protected function modelEventKey(Model $model, string $action): string
    {
        return str(class_basename($model))->snake()->value().'.'.$action;
    }

    protected function describeModel(Model $model, string $action): string
    {
        $label = $this->subjectLabel($model);

        return match ($action) {
            'created' => "{$label} created",
            'updated' => "{$label} updated",
            'deleted' => "{$label} deleted",
            'restored' => "{$label} restored",
            default => "{$label} {$action}",
        };
    }

    protected function subjectLabel(Model $model): string
    {
        return match (true) {
            $model instanceof Booking => 'Booking #'.$model->getKey(),
            $model instanceof JobCard => 'Job card #'.$model->getKey(),
            $model instanceof Customer => filled($model->full_name) ? $model->full_name : 'Customer #'.$model->getKey(),
            $model instanceof Vehicle => filled($model->registration_number) ? $model->registration_number : 'Vehicle #'.$model->getKey(),
            $model instanceof Employee => filled($model->full_name) ? $model->full_name : 'Employee #'.$model->getKey(),
            $model instanceof Commission => 'Commission #'.$model->getKey(),
            $model instanceof Setting => "Setting {$model->group}.{$model->key}",
            $model instanceof User => filled($model->name) ? $model->name : (string) $model->email,
            default => class_basename($model).' #'.$model->getKey(),
        };
    }

    /**
     * @return array<string, mixed>
     */
    protected function modelProperties(Model $model, string $action): array
    {
        if ($action === 'created') {
            return [
                'attributes' => $this->redactProperties($model->getAttributes()),
            ];
        }

        if ($action === 'updated') {
            $changes = collect($model->getChanges())
                ->except(config('activity_log.ignored_change_keys', []))
                ->all();

            $original = collect($model->getOriginal())
                ->only(array_keys($changes))
                ->all();

            return [
                'changes' => $this->redactProperties($changes),
                'original' => $this->redactProperties($original),
            ];
        }

        if (in_array($action, ['deleted', 'restored'], true)) {
            return [
                'id' => $model->getKey(),
            ];
        }

        return [];
    }

    /**
     * @param  array<string, mixed>  $properties
     * @return array<string, mixed>
     */
    protected function redactProperties(array $properties): array
    {
        $redacted = config('activity_log.redacted_attributes', []);

        foreach ($properties as $key => $value) {
            if (in_array($key, $redacted, true)) {
                $properties[$key] = '[redacted]';

                continue;
            }

            if (is_array($value)) {
                $properties[$key] = $this->redactProperties($value);
            }
        }

        return $properties;
    }

    protected function resolveBranchId(?Model $model = null): ?int
    {
        if ($model && isset($model->branch_id)) {
            return (int) $model->branch_id;
        }

        if (app()->bound(BranchService::class)) {
            return app(BranchService::class)->currentBranchId();
        }

        return null;
    }
}
