<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\JobCardController;
use App\Http\Controllers\ManifestController;
use App\Http\Controllers\ManualController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PerformanceController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
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
use App\Http\Controllers\Settings\UserController as SettingsUserController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Setup\SetupWizardController;
use App\Services\InstallService;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (app(InstallService::class)->isInstalled()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('setup.welcome');
});

Route::get('manifest.webmanifest', ManifestController::class)->name('manifest');

Route::middleware(['guest', 'not.installed'])->prefix('setup')->name('setup.')->group(function () {
    Route::redirect('/', '/setup/welcome');
    Route::get('welcome', [SetupWizardController::class, 'welcome'])->name('welcome');
    Route::post('welcome', [SetupWizardController::class, 'storeWelcome'])->name('welcome.store');
    Route::get('business', [SetupWizardController::class, 'business'])->name('business');
    Route::post('business', [SetupWizardController::class, 'storeBusiness'])->name('business.store');
    Route::get('branch', [SetupWizardController::class, 'branch'])->name('branch');
    Route::post('branch', [SetupWizardController::class, 'storeBranch'])->name('branch.store');
    Route::get('admin', [SetupWizardController::class, 'admin'])->name('admin');
    Route::post('admin', [SetupWizardController::class, 'storeAdmin'])->name('admin.store');
    Route::get('team', [SetupWizardController::class, 'team'])->name('team');
    Route::post('team', [SetupWizardController::class, 'storeTeam'])->name('team.store');
    Route::post('team/skip', [SetupWizardController::class, 'skipTeam'])->name('team.skip');
    Route::get('preferences', [SetupWizardController::class, 'preferences'])->name('preferences');
    Route::post('preferences', [SetupWizardController::class, 'storePreferences'])->name('preferences.store');
    Route::post('preferences/skip', [SetupWizardController::class, 'skipPreferences'])->name('preferences.skip');
});

Route::middleware(['installed', 'auth', 'verified', 'branch'])->group(function () {
    Route::middleware('permission:dashboard.view')->get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/branch/switch', [BranchController::class, 'switch'])->name('branch.switch');

    Route::prefix('settings')->name('settings.')->group(function () {
        Route::middleware('permission:settings.view')->group(function () {
            Route::get('company', [CompanyController::class, 'edit'])->name('company');
            Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
            Route::resource('payment-methods', PaymentMethodController::class)->only(['index', 'show']);
            Route::get('integrations', [IntegrationController::class, 'index'])->name('integrations.index');
            Route::get('business-hours', [BusinessHourController::class, 'edit'])->name('business-hours.edit');
        });

        Route::middleware('permission:settings.update')->group(function () {
            Route::put('company', [CompanyController::class, 'update'])->name('company.update');
            Route::get('roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
            Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update');
            Route::resource('payment-methods', PaymentMethodController::class)->except(['index', 'show']);
            Route::put('integrations', [IntegrationController::class, 'update'])->name('integrations.update');
            Route::put('business-hours', [BusinessHourController::class, 'update'])->name('business-hours.update');
        });

        Route::resource('branches', SettingsBranchController::class);
        Route::resource('users', SettingsUserController::class);
    });

    Route::resource('customers', CustomerController::class);
    Route::middleware('permission:customers.view')->group(function () {
        Route::get('customers-loyalty', [CustomerController::class, 'loyalty'])->name('customers.loyalty');
        Route::get('customers-feedback', [CustomerController::class, 'feedback'])->name('customers.feedback');
    });

    Route::middleware('permission:vehicles.manage')->group(function () {
        Route::get('vehicles/check-in', [VehicleController::class, 'checkIn'])->name('vehicles.check-in');
        Route::post('vehicles/{vehicle}/check-in', [VehicleController::class, 'processCheckIn'])->name('vehicles.process-check-in');
        Route::resource('vehicles', VehicleController::class)->only(['create', 'store', 'edit', 'update', 'destroy'])->whereNumber('vehicle');
    });
    Route::middleware('permission:vehicles.view')->group(function () {
        Route::get('vehicles/active', [VehicleController::class, 'active'])->name('vehicles.active');
        Route::get('vehicles/ready', [VehicleController::class, 'ready'])->name('vehicles.ready');
        Route::get('vehicles/history', [VehicleController::class, 'history'])->name('vehicles.history');
        Route::resource('vehicles', VehicleController::class)->only(['index', 'show'])->whereNumber('vehicle');
    });

    Route::middleware('permission:services.manage')->group(function () {
        Route::resource('services/categories', ServiceCategoryController::class)->except(['index', 'show'])->names('services.categories');
        Route::resource('services', ServiceController::class)->only(['create', 'store', 'edit', 'update', 'destroy'])->whereNumber('service');
        Route::resource('packages', PackageController::class)->only(['create', 'store', 'edit', 'update', 'destroy'])->whereNumber('package');
    });
    Route::middleware('permission:services.view')->group(function () {
        Route::resource('services/categories', ServiceCategoryController::class)->only(['index', 'show'])->names('services.categories');
        Route::resource('services', ServiceController::class)->only(['index', 'show'])->whereNumber('service');
        Route::resource('packages', PackageController::class)->only(['index', 'show'])->whereNumber('package');
    });

    Route::middleware('permission:bookings.manage')->group(function () {
        Route::resource('bookings', BookingController::class)->only(['create', 'store', 'edit', 'update', 'destroy'])->whereNumber('booking');
    });
    Route::middleware('permission:bookings.view')->group(function () {
        Route::get('bookings/calendar', [BookingController::class, 'calendar'])->name('bookings.calendar');
        Route::get('bookings/walk-ins', [BookingController::class, 'walkIns'])->name('bookings.walk-ins');
        Route::get('bookings/pending', [BookingController::class, 'pending'])->name('bookings.pending');
        Route::get('bookings/completed', [BookingController::class, 'completed'])->name('bookings.completed');
        Route::get('bookings/cancelled', [BookingController::class, 'cancelled'])->name('bookings.cancelled');
        Route::resource('bookings', BookingController::class)->only(['index', 'show'])->whereNumber('booking');
    });

    Route::middleware('permission:job-cards.manage')->group(function () {
        Route::patch('job-cards/{jobCard}/live-status', [JobCardController::class, 'updateLiveStatus'])->name('job-cards.live-status');
        Route::resource('job-cards', JobCardController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
    });
    Route::middleware('permission:job-cards.view')->group(function () {
        Route::get('job-cards/live', [JobCardController::class, 'live'])->name('job-cards.live');
        Route::get('job-cards/open', [JobCardController::class, 'open'])->name('job-cards.open');
        Route::get('job-cards/in-progress', [JobCardController::class, 'inProgress'])->name('job-cards.in-progress');
        Route::get('job-cards/completed', [JobCardController::class, 'completed'])->name('job-cards.completed');
        Route::resource('job-cards', JobCardController::class)->only(['index', 'show']);
    });

    Route::middleware('permission:inventory.view')->group(function () {
        Route::get('products/low-stock', [ProductController::class, 'lowStock'])->name('products.low-stock');
        Route::resource('products', ProductController::class)->only(['index', 'show']);
        Route::resource('suppliers', SupplierController::class)->only(['index', 'show']);
        Route::resource('purchase-orders', PurchaseOrderController::class)->only(['index', 'show']);
        Route::resource('stock-movements', StockMovementController::class)->only(['index', 'show'])->whereNumber('stock_movement');
    });
    Route::middleware('permission:inventory.manage')->group(function () {
        Route::resource('products', ProductController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
        Route::resource('suppliers', SupplierController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
        Route::resource('purchase-orders', PurchaseOrderController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
        Route::resource('stock-movements', StockMovementController::class)->only(['create', 'store'])->whereNumber('stock_movement');
    });

    Route::middleware('permission:pos.access')->group(function () {
        Route::get('pos', [PosController::class, 'index'])->name('pos.index');
        Route::post('pos/stk-push', [PosController::class, 'stkPush'])->name('pos.stk-push');
        Route::post('pos', [PosController::class, 'store'])->name('pos.store');
    });
    Route::middleware('permission:sales.view')->group(function () {
        Route::resource('invoices', InvoiceController::class)->only(['index', 'show']);
        Route::get('receipts', [ReceiptController::class, 'index'])->name('receipts.index');
        Route::resource('refunds', RefundController::class)->only(['index', 'show']);
    });
    Route::middleware('permission:pos.access,sales.view')->get('receipts/{receipt}', [ReceiptController::class, 'show'])->name('receipts.show');
    Route::middleware('permission:sales.manage')->group(function () {
        Route::resource('invoices', InvoiceController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
        Route::resource('refunds', RefundController::class)->only(['create', 'store']);
    });

    Route::middleware('permission:payments.view')->group(function () {
        Route::get('payments/cash', [PaymentController::class, 'cash'])->name('payments.cash');
        Route::get('payments/mpesa', [PaymentController::class, 'mpesa'])->name('payments.mpesa');
        Route::get('payments/card', [PaymentController::class, 'card'])->name('payments.card');
        Route::get('payments/bank', [PaymentController::class, 'bank'])->name('payments.bank');
        Route::resource('payments', PaymentController::class)->only(['index', 'show']);
    });

    Route::middleware('permission:staff.view')->group(function () {
        Route::resource('employees', EmployeeController::class)->only(['index', 'show']);
        Route::resource('attendance', AttendanceController::class)->only(['index', 'show']);
        Route::resource('commissions', CommissionController::class)->only(['index']);
        Route::resource('performance', PerformanceController::class)->only(['index']);
    });
    Route::middleware('permission:staff.manage')->group(function () {
        Route::resource('employees', EmployeeController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
        Route::resource('attendance', AttendanceController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
    });

    Route::middleware('permission:reports.view')->group(function () {
        Route::get('reports/daily', [ReportController::class, 'daily'])->name('reports.daily');
        Route::get('reports/weekly', [ReportController::class, 'weekly'])->name('reports.weekly');
        Route::get('reports/monthly', [ReportController::class, 'monthly'])->name('reports.monthly');
        Route::get('reports/revenue', [ReportController::class, 'revenue'])->name('reports.revenue');
        Route::get('reports/customers', [ReportController::class, 'customers'])->name('reports.customers');
        Route::get('reports/staff', [ReportController::class, 'staff'])->name('reports.staff');
        Route::get('reports/inventory', [ReportController::class, 'inventory'])->name('reports.inventory');
        Route::get('reports/job-cards', [ReportController::class, 'jobCards'])->name('reports.job-cards');
    });

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');

    Route::get('manual', [ManualController::class, 'index'])->name('manual.index');
    Route::post('onboarding/complete', [OnboardingController::class, 'complete'])->name('onboarding.complete');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
