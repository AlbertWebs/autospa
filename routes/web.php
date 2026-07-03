<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\EmailCampaignController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\JobCardController;
use App\Http\Controllers\Marketing\SmsCampaignController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PerformanceController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\RefundController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ServiceCategoryController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\Settings\BranchController as SettingsBranchController;
use App\Http\Controllers\Settings\BusinessHourController;
use App\Http\Controllers\Settings\CompanyController;
use App\Http\Controllers\Settings\IntegrationController;
use App\Http\Controllers\Settings\PaymentMethodController;
use App\Http\Controllers\Settings\RoleController;
use App\Http\Controllers\Settings\TaxController;
use App\Http\Controllers\Settings\UserController as SettingsUserController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware(['auth', 'verified', 'branch'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/branch/switch', [BranchController::class, 'switch'])->name('branch.switch');

    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('company', [CompanyController::class, 'edit'])->name('company');
        Route::put('company', [CompanyController::class, 'update'])->name('company.update');
        Route::resource('branches', SettingsBranchController::class);
        Route::resource('users', SettingsUserController::class);
        Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::resource('taxes', TaxController::class);
        Route::resource('payment-methods', PaymentMethodController::class);
        Route::get('integrations', [IntegrationController::class, 'index'])->name('integrations.index');
        Route::put('integrations', [IntegrationController::class, 'update'])->name('integrations.update');
        Route::get('business-hours', [BusinessHourController::class, 'edit'])->name('business-hours.edit');
        Route::put('business-hours', [BusinessHourController::class, 'update'])->name('business-hours.update');
    });

    Route::resource('customers', CustomerController::class);
    Route::get('customers-loyalty', [CustomerController::class, 'loyalty'])->name('customers.loyalty');
    Route::get('customers-feedback', [CustomerController::class, 'feedback'])->name('customers.feedback');

    Route::get('vehicles/check-in', [VehicleController::class, 'checkIn'])->name('vehicles.check-in');
    Route::get('vehicles/active', [VehicleController::class, 'active'])->name('vehicles.active');
    Route::get('vehicles/ready', [VehicleController::class, 'ready'])->name('vehicles.ready');
    Route::get('vehicles/history', [VehicleController::class, 'history'])->name('vehicles.history');
    Route::resource('vehicles', VehicleController::class);

    Route::resource('services/categories', ServiceCategoryController::class)->names('services.categories');
    Route::get('services/pricing', [ServiceController::class, 'pricing'])->name('services.pricing');
    Route::resource('services', ServiceController::class);
    Route::resource('packages', PackageController::class);

    Route::get('bookings/calendar', [BookingController::class, 'calendar'])->name('bookings.calendar');
    Route::get('bookings/walk-ins', [BookingController::class, 'walkIns'])->name('bookings.walk-ins');
    Route::get('bookings/pending', [BookingController::class, 'pending'])->name('bookings.pending');
    Route::get('bookings/completed', [BookingController::class, 'completed'])->name('bookings.completed');
    Route::get('bookings/cancelled', [BookingController::class, 'cancelled'])->name('bookings.cancelled');
    Route::resource('bookings', BookingController::class);

    Route::get('job-cards/open', [JobCardController::class, 'open'])->name('job-cards.open');
    Route::get('job-cards/in-progress', [JobCardController::class, 'inProgress'])->name('job-cards.in-progress');
    Route::get('job-cards/completed', [JobCardController::class, 'completed'])->name('job-cards.completed');
    Route::resource('job-cards', JobCardController::class);

    Route::get('products/low-stock', [ProductController::class, 'lowStock'])->name('products.low-stock');
    Route::resource('products', ProductController::class);
    Route::resource('suppliers', SupplierController::class);
    Route::resource('purchase-orders', PurchaseOrderController::class);
    Route::resource('stock-movements', StockMovementController::class)->only(['index', 'create', 'store', 'show']);

    Route::get('pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('pos', [PosController::class, 'store'])->name('pos.store');
    Route::resource('invoices', InvoiceController::class);
    Route::resource('receipts', ReceiptController::class)->only(['index', 'show']);
    Route::resource('refunds', RefundController::class)->only(['index', 'create', 'store', 'show']);

    Route::get('payments/cash', [PaymentController::class, 'cash'])->name('payments.cash');
    Route::get('payments/mpesa', [PaymentController::class, 'mpesa'])->name('payments.mpesa');
    Route::get('payments/card', [PaymentController::class, 'card'])->name('payments.card');
    Route::get('payments/bank', [PaymentController::class, 'bank'])->name('payments.bank');
    Route::resource('payments', PaymentController::class)->only(['index', 'show']);

    Route::resource('employees', EmployeeController::class);
    Route::resource('attendance', AttendanceController::class);
    Route::resource('commissions', CommissionController::class)->only(['index']);
    Route::resource('performance', PerformanceController::class)->only(['index']);

    Route::get('reports/daily', [ReportController::class, 'daily'])->name('reports.daily');
    Route::get('reports/weekly', [ReportController::class, 'weekly'])->name('reports.weekly');
    Route::get('reports/monthly', [ReportController::class, 'monthly'])->name('reports.monthly');
    Route::get('reports/revenue', [ReportController::class, 'revenue'])->name('reports.revenue');
    Route::get('reports/customers', [ReportController::class, 'customers'])->name('reports.customers');
    Route::get('reports/staff', [ReportController::class, 'staff'])->name('reports.staff');
    Route::get('reports/inventory', [ReportController::class, 'inventory'])->name('reports.inventory');

    Route::resource('promotions', PromotionController::class);
    Route::resource('marketing/sms', SmsCampaignController::class)->names('marketing.sms');
    Route::resource('marketing/email', EmailCampaignController::class)->names('marketing.email');
    Route::view('marketing/loyalty', 'marketing.loyalty')->name('marketing.loyalty');

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
