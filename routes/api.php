<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DashboardReportController;
use App\Http\Controllers\Api\ApprovedSubmissionController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/uplift/dashboard-reports', [DashboardReportController::class, 'upliftReports']);

Route::middleware('api.client')->prefix('v1/approved')->group(function () {
    Route::get('/indicator-submissions', [ApprovedSubmissionController::class, 'indicatorSubmissions']);
});
