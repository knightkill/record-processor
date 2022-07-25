<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::get('process_stage', \App\Http\Controllers\ProcessStageController::class)->name('process-stage');

Route::post('load_records', \App\Http\Controllers\LoadRecordsFromCSV::class)->name('load-records');

Route::get('validate_records', \App\Http\Controllers\ValidateRecordsController::class)->name('validate-records');
Route::get('insert_records', \App\Http\Controllers\InsertRecordsController::class)->name('insert-records');
Route::get('process_records', \App\Http\Controllers\ProcessRecordsController::class)->name('process-records');

Route::get('validate_status', [\App\Http\Controllers\StatusController::class,'validateStatus'] )->name('validate-status');
Route::get('process_status', [\App\Http\Controllers\StatusController::class,'processStatus'] )->name('process-status');
Route::get('insert_status', [\App\Http\Controllers\StatusController::class,'insertStatus'] )->name('insert-status');

Route::get('reset_state', \App\Http\Controllers\ResetStateController::class)->name('reset-state');
Route::get('download_processed_csv', \App\Http\Controllers\DownloadProcessedController::class)->name('download-processed-csv');

Route::get('validate_records_sse', \App\Http\Controllers\ValidateRecordsSSE::class)->name('validate-records-sse');
