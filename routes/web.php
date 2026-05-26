<?php

// use App\Http\Controllers\Teams\TeamInvitationController;
// use App\Http\Middleware\EnsureTeamMembership;
// use Illuminate\Support\Facades\Route;

// Route::inertia('/', 'Welcome')->name('home');

// Route::prefix('admin')
//     ->middleware(['auth', 'verified'])
//     ->group(function () {
//         Route::inertia('dashboard', 'Dashboard')->name('dashboard');
//     });





use App\Http\Controllers\BranchController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExtraServiceController;
use App\Http\Controllers\InsurancePlanController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleCategoryController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;

Route::

middleware(['auth', 'verified'])
->prefix('admin')
->name('admin.')
->group(function () {

    // invoke method
    Route::get('/dashboard', DashboardController::class)->name('dashboard');


    // ── Vehicles ──────────────────────────────────────────────
    Route::resource('vehicles', VehicleController::class);
    Route::patch('vehicles/{vehicle}/status', [VehicleController::class, 'updateStatus'])->name('vehicles.status');

    // ── Vehicle Categories ────────────────────────────────────
    Route::resource('vehicle-categories', VehicleCategoryController::class)
        ->except(['show'])
        ->names('categories');

    // ── Branches ──────────────────────────────────────────────
    Route::resource('branches', BranchController::class);

    // ── Customers ─────────────────────────────────────────────
    Route::resource('customers', CustomerController::class);
    Route::patch('customers/{customer}/blacklist', [CustomerController::class, 'toggleBlacklist'])->name('customers.blacklist');

    // ── Reservations ──────────────────────────────────────────
    Route::resource('reservations', ReservationController::class)->except(['update', 'destroy']);
    Route::patch('reservations/{reservation}/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');
    Route::post('reservations/calculate-price', [ReservationController::class, 'calculatePrice'])->name('reservations.price');

    // ── Rentals ───────────────────────────────────────────────
    Route::resource('rentals', RentalController::class)->except(['edit', 'update', 'destroy']);
    Route::post('rentals/from-reservation/{reservation}', [RentalController::class, 'storeFromReservation'])->name('rentals.from-reservation');
    Route::get('rentals/{rental}/return', [RentalController::class, 'returnForm'])->name('rentals.return');
    Route::post('rentals/{rental}/return', [RentalController::class, 'processReturn'])->name('rentals.process-return');
    Route::patch('rentals/{rental}/cancel', [RentalController::class, 'cancel'])->name('rentals.cancel');

    // ── Maintenance ───────────────────────────────────────────
    Route::get('maintenance', [MaintenanceController::class, 'index'])->name('maintenance.index');
    Route::post('maintenance', [MaintenanceController::class, 'store'])->name('maintenance.store');
    Route::patch('maintenance/{maintenance}/complete', [MaintenanceController::class, 'complete'])->name('maintenance.complete');

    // ── Settings (manager+) ───────────────────────────────────
    // Route::middleware('role:admin|manager')->group(function () {
    //     Route::resource('insurance-plans', InsurancePlanController::class)->except(['create', 'edit', 'show']);
    //     Route::resource('extra-services', ExtraServiceController::class)->except(['create', 'edit', 'show']);
    //     Route::resource('promotions', PromotionController::class)->except(['create', 'edit', 'show']);

    //     Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    //     Route::get('reports/revenue', [ReportController::class, 'revenue'])->name('reports.revenue');
    //     Route::get('reports/fleet', [ReportController::class, 'fleet'])->name('reports.fleet');
    // });

    // ── Admin only ────────────────────────────────────────────
    // Route::middleware('role:admin')->group(function () {
    //     Route::get('settings/users', [UserController::class, 'index'])->name('users.index');
    //     Route::post('settings/users', [UserController::class, 'store'])->name('users.store');
    //     Route::put('settings/users/{user}', [UserController::class, 'update'])->name('users.update');
    //     Route::delete('settings/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    // });
});

// require __DIR__ . '/auth.php';
require __DIR__.'/settings.php';

