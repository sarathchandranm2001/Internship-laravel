<?php

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\InstallerController;
use App\Http\Middleware\CheckInstallation;
use App\Models\User;

// Home route
Route::get('/', function () {
    if (!User::where('role', 'admin')->exists()) {
        return app(InstallerController::class)->showForm();
    }
    return view('welcome');
});

Route::post('/', [InstallerController::class, 'processForm']);

// Authenticated dashboard route (common for all users)
Route::get('/dashboard', function () {
    $user = Auth::user(); // Use the Auth facade explicitly
    return view('dashboard', ['user' => $user]);
})->middleware(['auth'])->name('dashboard');

// Profile management routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Employee routes
Route::middleware(['auth', 'isEmployee'])->group(function () {
    Route::get('/employee/dashboard', [EmployeeController::class, 'index'])->name('employee.dashboard');
    // Add more employee-specific routes here
});

// Client routes
Route::middleware(['auth', 'isClient'])->group(function () {
    Route::get('/client/dashboard', [ClientController::class, 'index'])->name('client.dashboard');
    // Add more client-specific routes here
});

// Admin routes (only accessible by admin)
Route::middleware(['auth', 'isAdmin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/manage-users', [AdminController::class, 'manageUsers'])->name('admin.manage-users');
    Route::get('/admin/manage-users/create', [AdminController::class, 'create'])->name('admin.users.create');
    Route::post('/admin/manage-users', [AdminController::class, 'store'])->name('admin.users.store');
    Route::get('/admin/manage-users/{user}/edit', [AdminController::class, 'edit'])->name('admin.users.edit');
    Route::patch('/admin/manage-users/{user}', [AdminController::class, 'update'])->name('admin.users.update');
    Route::delete('/admin/manage-users/{user}', [AdminController::class, 'destroy'])->name('admin.users.destroy');
    Route::get('/admin/view-reports', [AdminController::class, 'viewReports'])->name('admin.view-reports');
    Route::get('/admin/settings', [AdminController::class, 'settings'])->name('admin.settings');
    Route::post('/admin/settings', [AdminController::class, 'updateSettings'])->name('admin.update.settings');
});

// Include authentication routes
require __DIR__.'/auth.php';

