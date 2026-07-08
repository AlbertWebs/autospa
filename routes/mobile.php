<?php

use App\Http\Controllers\Mobile\MobileAttendanceController;
use App\Http\Controllers\Mobile\MobileBookingController;
use App\Http\Controllers\Mobile\MobileCommissionController;
use App\Http\Controllers\Mobile\MobileCustomerController;
use App\Http\Controllers\Mobile\MobileDashboardController;
use App\Http\Controllers\Mobile\MobileEmployeeController;
use App\Http\Controllers\Mobile\MobileFixedAssetController;
use App\Http\Controllers\Mobile\MobileInvoiceController;
use App\Http\Controllers\Mobile\MobileJobCardController;
use App\Http\Controllers\Mobile\MobileMenuController;
use App\Http\Controllers\Mobile\MobilePaymentController;
use App\Http\Controllers\Mobile\MobilePerformanceController;
use App\Http\Controllers\Mobile\MobilePosController;
use App\Http\Controllers\Mobile\MobileProductController;
use App\Http\Controllers\Mobile\MobilePurchaseOrderController;
use App\Http\Controllers\Mobile\MobileReceiptController;
use App\Http\Controllers\Mobile\MobileReportController;
use App\Http\Controllers\Mobile\MobileServiceController;
use App\Http\Controllers\Mobile\MobileSettingsController;
use App\Http\Controllers\Mobile\MobileStockMovementController;
use App\Http\Controllers\Mobile\MobileSupplierController;
use App\Http\Controllers\Mobile\MobileVehicleController;
use Illuminate\Support\Facades\Route;

Route::get('/', [MobileDashboardController::class, 'index'])
    ->middleware('permission:dashboard.view')
    ->name('index');

Route::get('/menu', [MobileMenuController::class, 'index'])->name('menu');

Route::middleware('permission:job-cards.view')->prefix('job-cards')->name('job-cards.')->group(function () {
    Route::get('/', [MobileJobCardController::class, 'index'])->name('index');
    Route::get('/live', [MobileJobCardController::class, 'live'])->name('live');
});

Route::middleware('permission:job-cards.manage')->prefix('job-cards')->name('job-cards.')->group(function () {
    Route::get('/create', [MobileJobCardController::class, 'create'])->name('create');
    Route::post('/', [MobileJobCardController::class, 'store'])->name('store');
    Route::patch('/{jobCard}/live-status', [MobileJobCardController::class, 'updateLiveStatus'])->name('live-status')->whereNumber('jobCard');
});

Route::middleware('permission:job-cards.view')->prefix('job-cards')->name('job-cards.')->group(function () {
    Route::get('/{jobCard}', [MobileJobCardController::class, 'show'])->name('show')->whereNumber('jobCard');
});

Route::middleware('permission:bookings.view')->prefix('bookings')->name('bookings.')->group(function () {
    Route::get('/', [MobileBookingController::class, 'index'])->name('index');
    Route::get('/calendar', [MobileBookingController::class, 'calendar'])->name('calendar');
    Route::get('/walk-ins', [MobileBookingController::class, 'walkIns'])->name('walk-ins');
    Route::get('/{booking}', [MobileBookingController::class, 'show'])->name('show')->whereNumber('booking');
});

Route::middleware('permission:vehicles.view')->prefix('vehicles')->name('vehicles.')->group(function () {
    Route::get('/', [MobileVehicleController::class, 'index'])->name('index');
    Route::get('/active', [MobileVehicleController::class, 'active'])->name('active');
    Route::get('/ready', [MobileVehicleController::class, 'ready'])->name('ready');
    Route::get('/{vehicle}', [MobileVehicleController::class, 'show'])->name('show')->whereNumber('vehicle');
});

Route::middleware('permission:customers.view')->prefix('customers')->name('customers.')->group(function () {
    Route::get('/', [MobileCustomerController::class, 'index'])->name('index');
    Route::get('/loyalty', [MobileCustomerController::class, 'loyalty'])->name('loyalty');
    Route::get('/feedback', [MobileCustomerController::class, 'feedback'])->name('feedback');
    Route::get('/{customer}', [MobileCustomerController::class, 'show'])->name('show')->whereNumber('customer');
});

Route::middleware('permission:pos.access')->get('/pos', [MobilePosController::class, 'index'])->name('pos.index');

Route::middleware('permission:sales.view')->group(function () {
    Route::get('/invoices', [MobileInvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}', [MobileInvoiceController::class, 'show'])->name('invoices.show')->whereNumber('invoice');
    Route::get('/receipts', [MobileReceiptController::class, 'index'])->name('receipts.index');
});

Route::middleware('permission:payments.view')->prefix('payments')->name('payments.')->group(function () {
    Route::get('/', [MobilePaymentController::class, 'index'])->name('index');
    Route::get('/cash', [MobilePaymentController::class, 'cash'])->name('cash');
    Route::get('/mpesa', [MobilePaymentController::class, 'mpesa'])->name('mpesa');
    Route::get('/card', [MobilePaymentController::class, 'card'])->name('card');
    Route::get('/bank', [MobilePaymentController::class, 'bank'])->name('bank');
});

Route::middleware('permission:inventory.view')->group(function () {
    Route::get('/products', [MobileProductController::class, 'index'])->name('products.index');
    Route::get('/products/low-stock', [MobileProductController::class, 'lowStock'])->name('products.low-stock');
    Route::get('/products/{product}', [MobileProductController::class, 'show'])->name('products.show')->whereNumber('product');
    Route::get('/suppliers', [MobileSupplierController::class, 'index'])->name('suppliers.index');
    Route::get('/purchase-orders', [MobilePurchaseOrderController::class, 'index'])->name('purchase-orders.index');
    Route::get('/stock-movements', [MobileStockMovementController::class, 'index'])->name('stock-movements.index');
    Route::get('/fixed-assets', [MobileFixedAssetController::class, 'index'])->name('fixed-assets.index');
});

Route::middleware('permission:staff.view')->group(function () {
    Route::get('/employees', [MobileEmployeeController::class, 'index'])->name('employees.index');
    Route::get('/commissions', [MobileCommissionController::class, 'index'])->name('commissions.index');
    Route::get('/performance', [MobilePerformanceController::class, 'index'])->name('performance.index');
});

Route::middleware(['permission:staff.view', 'attendance.enabled'])->group(function () {
    Route::get('/attendance', [MobileAttendanceController::class, 'index'])->name('attendance.index');
});

Route::middleware('permission:services.view')->group(function () {
    Route::get('/services', [MobileServiceController::class, 'index'])->name('services.index');
    Route::get('/service-categories', [MobileServiceController::class, 'categories'])->name('services.categories.index');
});

Route::middleware('permission:reports.view')->prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [MobileReportController::class, 'index'])->name('index');
    Route::get('/daily', [MobileReportController::class, 'daily'])->name('daily');
    Route::get('/weekly', [MobileReportController::class, 'weekly'])->name('weekly');
    Route::get('/monthly', [MobileReportController::class, 'monthly'])->name('monthly');
    Route::get('/revenue', [MobileReportController::class, 'revenue'])->name('revenue');
    Route::get('/profit', [MobileReportController::class, 'profit'])->name('profit');
    Route::get('/customers', [MobileReportController::class, 'customers'])->name('customers');
    Route::get('/staff', [MobileReportController::class, 'staff'])->name('staff');
    Route::get('/job-cards', [MobileReportController::class, 'jobCards'])->name('job-cards');
    Route::get('/inventory', [MobileReportController::class, 'inventory'])->name('inventory');
});

Route::middleware('permission:settings.view')->prefix('settings')->name('settings.')->group(function () {
    Route::get('/company', [MobileSettingsController::class, 'company'])->name('company');
    Route::get('/branches', [MobileSettingsController::class, 'branches'])->name('branches.index');
    Route::get('/users', [MobileSettingsController::class, 'users'])->name('users.index');
    Route::get('/roles', [MobileSettingsController::class, 'roles'])->name('roles.index');
    Route::get('/integrations', [MobileSettingsController::class, 'integrations'])->name('integrations.index');
});
