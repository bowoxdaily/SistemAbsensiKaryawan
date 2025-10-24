<?php

use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\SubDepartmentController;
use App\Http\Controllers\Admin\KaryawanController;
use App\Http\Controllers\Admin\PositionController;
use App\Http\Controllers\Admin\PayrollController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('departments')->group(function () {
    Route::get('/', [DepartmentController::class, 'index']);
    Route::post('/', [DepartmentController::class, 'store']);
    Route::get('/{id}', [DepartmentController::class, 'show']);
    Route::put('/{id}', [DepartmentController::class, 'update']);
    Route::delete('/{id}', [DepartmentController::class, 'destroy']);
});

Route::prefix('sub-departments')->group(function () {
    Route::get('/', [SubDepartmentController::class, 'list']);
    Route::post('/', [SubDepartmentController::class, 'store']);
    Route::get('/by-department/{departmentId}', [SubDepartmentController::class, 'getByDepartment']);
    Route::get('/{id}', [SubDepartmentController::class, 'show']);
    Route::put('/{id}', [SubDepartmentController::class, 'update']);
    Route::delete('/{id}', [SubDepartmentController::class, 'destroy']);
});

Route::prefix('karyawan')->group(function () {
    Route::get('/master-data', [KaryawanController::class, 'getMasterData']);
    Route::get('/', [KaryawanController::class, 'index']);
    Route::post('/', [KaryawanController::class, 'store']);
    Route::get('/{id}', [KaryawanController::class, 'show']);
    Route::put('/{id}', [KaryawanController::class, 'update']);
    Route::delete('/{id}', [KaryawanController::class, 'destroy']);
});

Route::prefix('positions')->group(function () {
    Route::get('/', [PositionController::class, 'index']);
    Route::post('/', [PositionController::class, 'store']);
    Route::get('/{id}', [PositionController::class, 'show']);
    Route::put('/{id}', [PositionController::class, 'update']);
    Route::delete('/{id}', [PositionController::class, 'destroy']);
});

Route::prefix('attendance')->group(function () {
    Route::get('/', [AttendanceController::class, 'list']);
    Route::get('/today/{employeeId}', [AttendanceController::class, 'getTodayAttendance']);
    Route::post('/check-in', [AttendanceController::class, 'checkIn']);
    Route::post('/check-out', [AttendanceController::class, 'checkOut']);
    Route::post('/verify-face', [AttendanceController::class, 'verifyFace']);
    Route::get('/summary', [AttendanceController::class, 'summary']);
    Route::post('/manual', [AttendanceController::class, 'manualEntry']);
});

// Admin Attendance API
Route::prefix('admin/attendance')->group(function () {
    Route::get('/{id}/detail', [AttendanceController::class, 'detail']);
});

// Employee Routes (for logged-in employees)
// Use web middleware to access session-based auth
Route::middleware('web')->prefix('employee')->group(function () {
    Route::get('/current', [\App\Http\Controllers\Employee\AttendanceController::class, 'getCurrentEmployee']);
    Route::get('/attendance/today', [\App\Http\Controllers\Employee\AttendanceController::class, 'getTodayAttendance']);
    Route::post('/attendance/check-in', [\App\Http\Controllers\Employee\AttendanceController::class, 'checkIn']);
    Route::post('/attendance/check-out', [\App\Http\Controllers\Employee\AttendanceController::class, 'checkOut']);
    Route::get('/attendance/history', [\App\Http\Controllers\Employee\AttendanceController::class, 'history']);
    Route::get('/attendance/summary', [\App\Http\Controllers\Employee\AttendanceController::class, 'summary']);
    Route::get('/attendance/{id}/detail', [\App\Http\Controllers\Employee\AttendanceController::class, 'detail']);

    // Employee Payroll API
    Route::get('/payroll', [\App\Http\Controllers\Employee\PayrollController::class, 'list']);
    Route::get('/payroll/statistics', [\App\Http\Controllers\Employee\PayrollController::class, 'statistics']);
    Route::get('/payroll/{id}', [\App\Http\Controllers\Employee\PayrollController::class, 'show']);
});

// Payroll API Routes
Route::prefix('payroll')->group(function () {
    Route::get('/', [PayrollController::class, 'list']);
    Route::post('/', [PayrollController::class, 'store']);
    Route::get('/employees', [PayrollController::class, 'getEmployees']);
    Route::get('/{id}', [PayrollController::class, 'show']);
    Route::put('/{id}', [PayrollController::class, 'update']);
    Route::delete('/{id}', [PayrollController::class, 'destroy']);
    Route::post('/{id}/send', [PayrollController::class, 'sendNotification']);
});
