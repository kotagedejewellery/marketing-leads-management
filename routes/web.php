<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeadController;
use App\Http\Middleware\EnsureAdmin;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard')->name('home');

Route::middleware('auth')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('leads', [DashboardController::class, 'leads'])->name('leads.index');
    Route::post('leads', [LeadController::class, 'store'])->middleware(EnsureAdmin::class)->name('leads.store');
    Route::post('leads/import', [LeadController::class, 'import'])->middleware(EnsureAdmin::class)->name('leads.import');
    Route::post('leads/assign', [LeadController::class, 'assign'])->middleware(EnsureAdmin::class)->name('leads.assign');
    Route::get('leads/{lead}', [LeadController::class, 'show'])->name('leads.show');
    Route::patch('leads/{lead}', [LeadController::class, 'update'])->name('leads.update');
    Route::patch('leads/{lead}/archive', [LeadController::class, 'archive'])->middleware(EnsureAdmin::class)->name('leads.archive');
    Route::patch('leads/{lead}/restore', [LeadController::class, 'restore'])->middleware(EnsureAdmin::class)->name('leads.restore');
    Route::get('statistics', [DashboardController::class, 'statistics'])->name('statistics');
    Route::middleware(EnsureAdmin::class)->group(function () {
        Route::get('branches', [DashboardController::class, 'branches'])->name('branches.index');
        Route::post('branches', [DashboardController::class, 'storeBranch'])->name('branches.store');
        Route::patch('branches/{branch}', [DashboardController::class, 'updateBranch'])->name('branches.update');
        Route::delete('branches/{branch}', [DashboardController::class, 'destroyBranch'])->name('branches.destroy');
        Route::get('accounts', [DashboardController::class, 'accounts'])->name('accounts.index');
        Route::post('accounts', [DashboardController::class, 'storeAccount'])->name('accounts.store');
        Route::patch('accounts/{account}', [DashboardController::class, 'updateAccount'])->name('accounts.update');
    });
});

