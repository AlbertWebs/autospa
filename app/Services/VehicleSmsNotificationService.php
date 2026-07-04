<?php

namespace App\Services;

use App\Data\Integrations\SmsResult;
use App\Data\Integrations\SmsMessage;
use App\Models\Customer;
use App\Models\Setting;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Log;
use Throwable;

class VehicleSmsNotificationService
{
    public function __construct(
        protected IntegrationService $integrationService,
    ) {}

    public function sendVehicleRegistered(Customer $customer, Vehicle $vehicle): ?SmsResult
    {
        return $this->sendToCustomer(
            $customer,
            sprintf(
                'Hello %s, your vehicle %s has been registered at AutoSpa.',
                $this->customerName($customer),
                $vehicle->registration_number
            ),
            'vehicle registration',
            $vehicle
        );
    }

    public function sendVehicleReadyForCollection(Customer $customer, Vehicle $vehicle): ?SmsResult
    {
        return $this->sendToCustomer(
            $customer,
            sprintf(
                'Hello %s, your vehicle %s is ready for collection at AutoSpa.',
                $this->customerName($customer),
                $vehicle->registration_number
            ),
            'vehicle ready for collection',
            $vehicle
        );
    }

    public function sendVehicleCollected(Customer $customer, ?Vehicle $vehicle = null): ?SmsResult
    {
        $message = $vehicle
            ? sprintf(
                'Hello %s, payment for vehicle %s has been completed and the vehicle has been marked as collected. Thank you for choosing AutoSpa.',
                $this->customerName($customer),
                $vehicle->registration_number
            )
            : sprintf(
                'Hello %s, your AutoSpa payment has been completed and your vehicle has been marked as collected. Thank you for choosing AutoSpa.',
                $this->customerName($customer)
            );

        return $this->sendToCustomer(
            $customer,
            $message,
            'vehicle collected',
            $vehicle
        );
    }

    public function smsEnabled(): bool
    {
        return filter_var(
            Setting::getValue('sms', 'enabled', false),
            FILTER_VALIDATE_BOOLEAN
        );
    }

    protected function sendToCustomer(
        Customer $customer,
        string $message,
        string $context,
        ?Vehicle $vehicle = null
    ): ?SmsResult {
        if (! $this->smsEnabled()) {
            return null;
        }

        $phone = trim((string) ($customer->phone ?? ''));

        if ($phone === '') {
            return null;
        }

        try {
            return $this->integrationService->sms()->send(
                new SmsMessage($phone, $message)
            );
        } catch (Throwable $e) {
            Log::warning('Vehicle SMS notification failed.', [
                'context' => $context,
                'customer_id' => $customer->id,
                'vehicle_id' => $vehicle?->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    protected function customerName(Customer $customer): string
    {
        $name = trim((string) $customer->full_name);

        return $name !== '' ? $name : 'customer';
    }
}
