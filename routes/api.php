<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ActualMaster;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GoogleSheetsController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SprintSummaryController;
use App\Http\Controllers\UsersController;

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

Route::group(['prefix' => 'v1'], function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('reset_Password');
    Route::post('/update-password', [AuthController::class, 'submitResetPassword'])->name('update.password.post');
    Route::post('/verify-reset-password', [AuthController::class, 'verifyResetPasswordLink'])->name('verify-reset-password');

    //Google-sheet related APIs
    Route::post('/get-actual-master', [ActualMaster::class, 'getActualMasterData']);
    Route::post('/refresh-actual-master', [GoogleSheetsController::class, 'readSheet']);
    Route::post('/get-change-logs-data', [GoogleSheetsController::class, 'readChangeLogsData']);
    Route::post('/get-change-logs', [GoogleSheetsController::class, 'readChangeLogsSheet']);
    Route::post('/get-sprint-summary-weightage-view', [SprintSummaryController::class, 'getTeamWiseStatusTotalSprintSummary']);
    Route::post('/get-dashboard-summary-view',[ActualMaster::class,'getTeamWiseProjectionDateSummary']);
    Route::post('/get-dashboard-view', [DashboardController::class, 'getDashboardData']);
    Route::post('/get-projects',[ProjectController::class, 'getProjectsList']);

    Route::group(['middleware' => ['token.valid']], function () {

        Route::post('/get-phases',[ActualMaster::class,'getPhasesList']);
        Route::post('/clear-change-log-sheet',[GoogleSheetsController::class,'clearChangeLogSheet']);

        //Profile APIs
        Route::post('/change-password', [AuthController::class, 'changePassword']);
        Route::post('/user/show', [AuthController::class, 'getUser']);
        //Profile
        Route::put('/user/update', [AuthController::class, 'updateProfile']);
        //User accounts
        Route::put('/user/update/{id}', [UsersController::class, 'updateUsers']);

        //Project APIs
        Route::post('/project/create', [ProjectController::class, 'createProject']);
    });
});
