<?php

use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\KaryawanController;
use App\Http\Controllers\Admin\PositionController;
use App\Http\Controllers\Admin\OfficeSettingController;
use App\Http\Controllers\Admin\WorkScheduleController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\LoginController;

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/admin/department', [DepartmentController::class, 'dashboard'])->name('admin.department.index');
    Route::get('/admin/karyawan', [KaryawanController::class, 'dashboard'])->name('admin.karyawan.index');
    Route::get('/admin/karyawan/export', [KaryawanController::class, 'export'])->name('admin.karyawan.export');
    Route::post('/admin/karyawan/import', [KaryawanController::class, 'import'])->name('admin.karyawan.import');
    Route::get('/admin/karyawan/template', [KaryawanController::class, 'downloadTemplate'])->name('admin.karyawan.template');
    Route::get('/admin/positions', [PositionController::class, 'dashboard'])->name('admin.positions.index');
    Route::get('/admin/attendance', [AttendanceController::class, 'index'])->name('admin.attendance.index');

    // Office Settings
    Route::get('/admin/settings/office', [OfficeSettingController::class, 'index'])->name('admin.settings.office');
    Route::post('/admin/settings/office', [OfficeSettingController::class, 'update'])->name('admin.settings.office.update');
    Route::get('/admin/settings/office/show', [OfficeSettingController::class, 'show'])->name('admin.settings.office.show');

    // Work Schedule Settings
    Route::get('/admin/settings/work-schedule', [WorkScheduleController::class, 'index'])->name('admin.settings.work-schedule');
    Route::post('/admin/settings/work-schedule', [WorkScheduleController::class, 'store'])->name('admin.settings.work-schedule.store');
    Route::get('/admin/settings/work-schedule/{id}', [WorkScheduleController::class, 'show'])->name('admin.settings.work-schedule.show');
    Route::put('/admin/settings/work-schedule/{id}', [WorkScheduleController::class, 'update'])->name('admin.settings.work-schedule.update');
    Route::delete('/admin/settings/work-schedule/{id}', [WorkScheduleController::class, 'destroy'])->name('admin.settings.work-schedule.destroy');
    Route::post('/admin/settings/work-schedule/{id}/toggle', [WorkScheduleController::class, 'toggleStatus'])->name('admin.settings.work-schedule.toggle');

    // Employee Routes
    Route::get('/employee/attendance', [\App\Http\Controllers\Employee\AttendanceController::class, 'index'])->name('employee.attendance.index');



    // Master Data Routes (Commented - Controllers not yet created)
    // Route::prefix('master')->group(function () {
    //     Route::resource('karyawan', \App\Http\Controllers\KaryawanController::class);
    //     Route::resource('departemen', \App\Http\Controllers\DepartemenController::class);
    //     Route::resource('jabatan', \App\Http\Controllers\JabatanController::class);
    // });

    // Absensi Routes (Commented - Controllers not yet created)
    // Route::prefix('absensi')->group(function () {
    //     Route::get('/', [\App\Http\Controllers\AbsensiController::class, 'index'])->name('absensi.index');
    //     Route::post('/store', [\App\Http\Controllers\AbsensiController::class, 'store'])->name('absensi.store');
    //     Route::get('/rekap', [\App\Http\Controllers\RekapController::class, 'index'])->name('rekap.index');
    // });

    // Settings Routes (Commented - Controllers not yet created)
    // Route::prefix('settings')->group(function () {
    //     Route::get('/jam-kerja', [\App\Http\Controllers\JamKerjaController::class, 'index'])->name('jam-kerja.index');
    //     Route::resource('users', \App\Http\Controllers\UserController::class);
    //     Route::get('/', [\App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index');
    // });

    // Profile Routes (Commented - Controllers not yet created)
    // Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    // Route::put('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
});
