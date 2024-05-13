
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ManagerControllers\EmployeeController;
use App\Http\Controllers\ManagerControllers\FireDrillController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ManagerControllers\DashboardController;

Route::middleware('auth:sanctum')->prefix('manager')->group(function () {

    Route::post('attendance', [AttendanceController::class, 'store']); // Attendance
    Route::get('time_logs', [DashboardController::class, 'getAttendance']); // Attendance
    Route::get('staff_attendance', [AttendanceController::class, 'staffAttendance']); // Attendance

    Route::resource('fire_drills', FireDrillController::class)->only([
        'store',
    ]);

    Route::apiResources([
        'employees' => EmployeeController::class,
    ]);
});
