<?php

use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\SubDepartmentController;
use App\Http\Controllers\Admin\KaryawanController;
use App\Http\Controllers\Admin\PositionController;
use App\Http\Controllers\Admin\OfficeSettingController;
use App\Http\Controllers\Admin\WorkScheduleController;
use App\Http\Controllers\Admin\CronJobController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\LoginController;

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes (with rate limiting to prevent brute force)
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1'); // Max 5 attempts per minute
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    // Dashboard (accessible by all authenticated users)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Admin Only Routes (protected by admin middleware)
    Route::middleware(['admin'])->group(function () {
        // Master Data
        Route::get('/admin/department', [DepartmentController::class, 'dashboard'])->name('admin.department.index');
        Route::get('/admin/sub-departments', [SubDepartmentController::class, 'index'])->name('admin.sub-departments.index');
        Route::get('/admin/karyawan', [KaryawanController::class, 'dashboard'])->name('admin.karyawan.index');
        Route::get('/admin/karyawan/export', [KaryawanController::class, 'export'])->name('admin.karyawan.export');
        Route::post('/admin/karyawan/import', [KaryawanController::class, 'import'])->name('admin.karyawan.import');
        Route::get('/admin/karyawan/template', [KaryawanController::class, 'downloadTemplate'])->name('admin.karyawan.template');
        Route::get('/admin/positions', [PositionController::class, 'dashboard'])->name('admin.positions.index');

        // Attendance Routes
        Route::get('/admin/attendance', [AttendanceController::class, 'index'])->name('admin.attendance.index');
        Route::get('/admin/attendance/face-detection', [AttendanceController::class, 'faceDetection'])->name('admin.attendance.face-detection');
        Route::get('/admin/attendance/report', [AttendanceController::class, 'report'])->name('admin.attendance.report');
        Route::get('/admin/attendance/export', [AttendanceController::class, 'export'])->name('admin.attendance.export');
        Route::delete('/admin/attendance/{id}', [AttendanceController::class, 'destroy'])->name('admin.attendance.destroy');

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

        // Cron Job Settings
        Route::get('/admin/settings/cronjob', [CronJobController::class, 'index'])->name('admin.settings.cronjob');
        Route::post('/admin/settings/cronjob/test', [CronJobController::class, 'testCommand'])->name('admin.settings.cronjob.test');
        Route::post('/admin/settings/cronjob/run', [CronJobController::class, 'runScheduler'])->name('admin.settings.cronjob.run');
        Route::get('/admin/settings/cronjob/list', [CronJobController::class, 'getScheduleList'])->name('admin.settings.cronjob.list');
        Route::get('/admin/settings/cronjob/status', [CronJobController::class, 'checkStatus'])->name('admin.settings.cronjob.status');
        Route::get('/admin/settings/cronjob/command', [CronJobController::class, 'getCronCommand'])->name('admin.settings.cronjob.command');

        // WhatsApp Settings
        Route::get('/admin/settings/whatsapp', [\App\Http\Controllers\Admin\WhatsAppSettingController::class, 'index'])->name('admin.settings.whatsapp');
        Route::post('/admin/settings/whatsapp', [\App\Http\Controllers\Admin\WhatsAppSettingController::class, 'update'])->name('admin.settings.whatsapp.update');
        Route::post('/admin/settings/whatsapp/test-connection', [\App\Http\Controllers\Admin\WhatsAppSettingController::class, 'testConnection'])->name('admin.settings.whatsapp.test-connection');
        Route::post('/admin/settings/whatsapp/send-test', [\App\Http\Controllers\Admin\WhatsAppSettingController::class, 'sendTest'])->name('admin.settings.whatsapp.send-test');
        Route::post('/admin/settings/whatsapp/reset-templates', [\App\Http\Controllers\Admin\WhatsAppSettingController::class, 'resetTemplates'])->name('admin.settings.whatsapp.reset-templates');

        // Leave Management
        Route::get('/admin/leave', [\App\Http\Controllers\Admin\LeaveController::class, 'index'])->name('admin.leave.index');
        Route::get('/admin/leave/{id}', [\App\Http\Controllers\Admin\LeaveController::class, 'show'])->name('admin.leave.show');
        Route::post('/admin/leave/{id}/approve', [\App\Http\Controllers\Admin\LeaveController::class, 'approve'])->name('admin.leave.approve');
        Route::post('/admin/leave/{id}/reject', [\App\Http\Controllers\Admin\LeaveController::class, 'reject'])->name('admin.leave.reject');
        Route::delete('/admin/leave/{id}', [\App\Http\Controllers\Admin\LeaveController::class, 'destroy'])->name('admin.leave.destroy');
    });

    // Employee Routes
    Route::get('/employee/attendance', [\App\Http\Controllers\Employee\AttendanceController::class, 'index'])->name('employee.attendance.index');
    Route::get('/employee/attendance/history', [\App\Http\Controllers\Employee\AttendanceController::class, 'historyPage'])->name('employee.attendance.history');

    // Employee Leave Routes
    Route::get('/employee/leave', [\App\Http\Controllers\Employee\LeaveController::class, 'index'])->name('employee.leave.index');
    Route::post('/employee/leave', [\App\Http\Controllers\Employee\LeaveController::class, 'store'])->name('employee.leave.store');
    Route::delete('/employee/leave/{id}', [\App\Http\Controllers\Employee\LeaveController::class, 'cancel'])->name('employee.leave.cancel');

    // Employee Profile Routes (with rate limiting)
    Route::middleware('throttle:60,1')->group(function () {
        Route::get('/employee/profile', [\App\Http\Controllers\Employee\ProfileController::class, 'index'])->name('employee.profile.index');
        Route::put('/employee/profile', [\App\Http\Controllers\Employee\ProfileController::class, 'update'])->name('employee.profile.update');
        Route::put('/employee/profile/photo', [\App\Http\Controllers\Employee\ProfileController::class, 'updatePhoto'])->name('employee.profile.update-photo');
        Route::put('/employee/profile/password', [\App\Http\Controllers\Employee\ProfileController::class, 'updatePassword'])->name('employee.profile.update-password');
    });

    // Admin Profile Routes (with middleware and rate limiting)
    Route::middleware(['admin', 'throttle:60,1'])->group(function () {
        Route::get('/admin/profile', [\App\Http\Controllers\Admin\AdminProfileController::class, 'index'])->name('admin.profile.index');
        Route::put('/admin/profile', [\App\Http\Controllers\Admin\AdminProfileController::class, 'update'])->name('admin.profile.update');
        Route::put('/admin/profile/photo', [\App\Http\Controllers\Admin\AdminProfileController::class, 'updatePhoto'])->name('admin.profile.update-photo');
        Route::put('/admin/profile/password', [\App\Http\Controllers\Admin\AdminProfileController::class, 'updatePassword'])->name('admin.profile.update-password');
    });



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
