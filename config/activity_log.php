<?php

use App\Models\Attendance;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Commission;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\Package;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Receipt;
use App\Models\RecurringBookingRule;
use App\Models\Refund;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Tax;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\JobCard;

return [

    'enabled' => env('ACTIVITY_LOG_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Models observed for create / update / delete / restore events
    |--------------------------------------------------------------------------
    */
    'observed_models' => [
        Attendance::class,
        Booking::class,
        Branch::class,
        Commission::class,
        Company::class,
        Customer::class,
        Employee::class,
        Invoice::class,
        JobCard::class,
        Package::class,
        Payment::class,
        PaymentMethod::class,
        Product::class,
        PurchaseOrder::class,
        Receipt::class,
        RecurringBookingRule::class,
        Refund::class,
        Service::class,
        ServiceCategory::class,
        Setting::class,
        StockMovement::class,
        Supplier::class,
        Tax::class,
        User::class,
        Vehicle::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Attributes never stored in activity properties
    |--------------------------------------------------------------------------
    */
    'redacted_attributes' => [
        'password',
        'pin',
        'remember_token',
        'otp',
        'payout_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ],

    /*
    |--------------------------------------------------------------------------
    | Model updates that only touch these keys are not logged
    |--------------------------------------------------------------------------
    */
    'ignored_change_keys' => [
        'updated_at',
        'remember_token',
    ],

];
