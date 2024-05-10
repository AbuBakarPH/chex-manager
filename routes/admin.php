<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\FieldController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\CqcVisitController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\MythBusterController;
use App\Http\Controllers\TaskSectionController;
use App\Http\Controllers\QuestionRiskController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\ThemeSettingController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\SectionQuestionController;
use App\Http\Controllers\RiskConversationController;
use App\Http\Controllers\AdminControllers\MediaController;
use App\Http\Controllers\ManagerControllers\RiskController;
use App\Http\Controllers\ManagerControllers\RoleController;
use App\Http\Controllers\ManagerControllers\TeamController;
use App\Http\Controllers\ManagerControllers\UserController;
use App\Http\Controllers\AdminControllers\CompanyController;
use App\Http\Controllers\AdminControllers\CategoryController;
use App\Http\Controllers\AdminControllers\CompanyIpController;
use App\Http\Controllers\AdminControllers\Auth\AdminController;
use App\Http\Controllers\AdminControllers\PackagePlanController;
use App\Http\Controllers\AdminControllers\SubCategoryController;
use App\Http\Controllers\ManagerControllers\DashboardController;
use App\Http\Controllers\ManagerControllers\PermissionController;
use App\Http\Controllers\ManagerControllers\RiskConfigController;
use App\Http\Controllers\ManagerControllers\NotificationController;
use App\Http\Controllers\OrganizationalRoleController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
//     return $request->user();
// });


// Admin Route

// Route::post('/login', [AdminController::class, 'login']);


Route::controller(AdminController::class)->prefix('admin')->group(function () {
    Route::post('/user/login', 'userLogin');
    Route::post('/user/verify-email', 'verifyEmail');
    Route::post('/user/verify-otp', 'verifyOtp');
    Route::post('/user/update-password', 'updatePassword');
});



Route::middleware('auth:sanctum')->prefix('admin')->group(function () {

    // Route::middleware('checkStorageLimit', 'transaction')->group(function () {

        Route::post('/logout', [AdminController::class, 'logout']);

        Route::controller(UserController::class)->group(function () {
            Route::get('/user/profile', 'getUserDetail');
            Route::get('/staff/list', 'getStaffList'); // Only Staff Role List
            Route::put('/user/password/{id}', 'updateUserPassword'); // Update User password by Admin / Manager
            Route::get('/user/role/{role}', 'getUserRole'); // Role Will be Approval or something else 
            Route::put('/allow/notication', 'allowNotification'); // This will toggle allow notifications
        });

        Route::controller(NotificationController::class)->group(function () {
            Route::post('/notification/status', 'updateNotificationStatus'); // get Detail of daily checklist
            Route::get('/notification/list', 'notificationList'); // get All Notification
        });

        Route::controller(DashboardController::class)->group(function () {
            Route::get('/dashboard-stats', 'getStats'); //manager main dashboard stat's data
            Route::get('/dashboard-risk-stats', 'getRiskStats'); //manager dashboard stat's data
            Route::get('/current-risk-level-stats', 'getCurrentLevelRiskStats'); //manager dashboard stat's data
            Route::get('/dashboard-data', 'getRisksData'); //manager dashboard stat's data
            Route::get('dashboard/setting', 'getDashboard');
        });

        Route::controller(TaskController::class)->group(function () {
            Route::put('/tasks/publish/{id}', 'updateStatus'); // get all risk reports
            Route::get('/tasks/for-config', 'getTasksForConfig'); // Tasks for config
            Route::get('/tasks/task-requests', 'getTaskRequests'); // Tasks Requests Templates
            Route::put('/task/status/{id}', 'setTaskStatus'); // Make Active Status
            Route::post('/task/clone', 'taskClone'); // Make Task Clone
            Route::put('/daily/checklist/{id}', 'updateStatusDailyChecklist'); // Daily Checklist

        });

        Route::controller(QuestionRiskController::class)->group(function () {
            Route::post('/risks/create', 'createRisk'); // Only for Manager
            Route::post('/risks/update/{id}', 'updateRisk'); // Only for Manager
            Route::get('/risk/requests', 'listRequests'); // Only for Manager
            Route::get('/risks/csv-data', 'csv_data'); // Make Task Clone
        });

        Route::controller(SubscriptionController::class)->group(function () {
            Route::get('current-subscribed-plan', 'getSubscribedPlanDetail'); // Tasks for config
            Route::get('package-plan/{id}/company', 'subscribedPlan'); // List of Company Who Subscibed Plan

        });

        Route::controller(AttendanceController::class)->group(function () {
            Route::get('check-ins/edit/{check_in}', 'edit');
            Route::get('attendance/time-logs', 'fetchTimeLogs');
        });

        Route::controller(ConfigController::class)->group(function () {
            Route::put('/configs/status/{id}', 'updateConfigStatus');   // Make Active Status
            Route::put('remove/assignee', 'removeAssignee');            // For Update Status
            Route::get('refain/task/{taskId}', 'checkRefainTask');      // Check Checklist Refain TIme
        });

        Route::get('/daily/schedule', [ScheduleController::class, 'index']); // Daily Checklist New API 
        Route::get('/permission', [PermissionController::class, 'index']); // Daily Checklist New API 
        Route::get('/notification', [NotificationController::class, 'index']); // Daily Checklist New API 
        Route::post('/subscribe', [SubscriptionController::class, 'store']); // Daily Checklist New API 


        Route::apiResource('theme-setting', ThemeSettingController::class)->only('index', 'store');
        Route::apiResource('schedule', ScheduleController::class)->only('index', 'show');
        Route::apiResource('audits', AuditController::class)->only('index');
        Route::get('/checklist/categories', [CategoryController::class, 'getFilteredCategoriesWithChecklists']); // get all categories and sub categories chich have checklists



        Route::apiResources([
            'organizational_roles'  => OrganizationalRoleController::class,
            'category'  => CategoryController::class,
            'company'   => CompanyController::class,
            'media' => MediaController::class,
            'role'  => RoleController::class,
            'user'  => UserController::class,
            'company-ip-address'  => CompanyIpController::class,
            'team'  => TeamController::class,
            'risk-configs'  => RiskConfigController::class,
            'package-plan'  => PackagePlanController::class,
        
            // New Routes
            'tasks'        => TaskController::class,
            'task/sections' => TaskSectionController::class,
            'section/questions' => SectionQuestionController::class,
            'fields' => FieldController::class,
            'configs' => ConfigController::class,
            'risks' => QuestionRiskController::class,
            'risk/conversations' => RiskConversationController::class,
            'myth-busters' => MythBusterController::class,
            'check-ins' => AttendanceController::class,
            'cqc-visits' => CqcVisitController::class,
            'suppliers' => SupplierController::class,

        ]);
    // });
});
