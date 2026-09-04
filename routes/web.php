<?php

use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\BitacoraController;
use App\Http\Controllers\Catalog\ActivityTypeController;
use App\Http\Controllers\Catalog\BranchController;
use App\Http\Controllers\Catalog\EmployeeController;
use App\Http\Controllers\Catalog\FolioController;
use App\Http\Controllers\Catalog\PaymentCatalogController;
use App\Http\Controllers\ExpenseReportController;
use App\Http\Controllers\SalaryReportController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    // Bitácoras Management
    Route::resource('bitacoras', BitacoraController::class);

    // Salary & Payroll Calculation
    Route::get('salaries', [SalaryReportController::class, 'index'])->name('salaries.index');

    // Expense Reports
    Route::get('expenses', [ExpenseReportController::class, 'index'])->name('expenses.index');

    // Catalog Management
    Route::prefix('catalogs')->name('catalogs.')->group(function () {
        // Branches
        Route::resource('branches', BranchController::class)->except(['create', 'show', 'edit']);

        // Employees
        Route::resource('employees', EmployeeController::class)->except(['create', 'show', 'edit']);

        // Activity Types
        Route::resource('activities', ActivityTypeController::class)->except(['create', 'show', 'edit']);

        // Folios & Consecutivos
        Route::resource('folios', FolioController::class)->except(['create', 'show', 'edit']);

        // Payment Methods, Card Types, and Payment Cards
        Route::get('payment-methods', [PaymentCatalogController::class, 'index'])->name('payment-methods.index');
        Route::post('payment-methods/method', [PaymentCatalogController::class, 'storeMethod'])->name('payment-methods.store-method');
        Route::put('payment-methods/method/{method}', [PaymentCatalogController::class, 'updateMethod'])->name('payment-methods.update-method');
        Route::delete('payment-methods/method/{method}', [PaymentCatalogController::class, 'destroyMethod'])->name('payment-methods.destroy-method');

        Route::post('payment-methods/card-type', [PaymentCatalogController::class, 'storeCardType'])->name('payment-methods.store-card-type');
        Route::put('payment-methods/card-type/{cardType}', [PaymentCatalogController::class, 'updateCardType'])->name('payment-methods.update-card-type');
        Route::delete('payment-methods/card-type/{cardType}', [PaymentCatalogController::class, 'destroyCardType'])->name('payment-methods.destroy-card-type');

        Route::post('payment-methods/card', [PaymentCatalogController::class, 'storeCard'])->name('payment-methods.store-card');
        Route::put('payment-methods/card/{card}', [PaymentCatalogController::class, 'updateCard'])->name('payment-methods.update-card');
        Route::delete('payment-methods/card/{card}', [PaymentCatalogController::class, 'destroyCard'])->name('payment-methods.destroy-card');

        // Clients & Client Branches
        Route::resource('clients', \App\Http\Controllers\Catalog\ClientController::class)->except(['create', 'show', 'edit']);
        Route::post('clients/{client}/branches', [\App\Http\Controllers\Catalog\ClientController::class, 'storeBranch'])->name('clients.branches.store');
        Route::put('clients/branches/{branch}', [\App\Http\Controllers\Catalog\ClientController::class, 'updateBranch'])->name('clients.branches.update');
        Route::delete('clients/branches/{branch}', [\App\Http\Controllers\Catalog\ClientController::class, 'destroyBranch'])->name('clients.branches.destroy');
    });

    // Administration (Users, Roles & Permissions)
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class)->except(['create', 'show', 'edit']);
        Route::resource('roles', RoleController::class)->except(['create', 'show', 'edit']);
    });
});

require __DIR__.'/settings.php';
