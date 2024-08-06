<?php

use App\Http\Controllers\Api\HolidayController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('holiday/{holiday}/download', [HolidayController::class, 'download']);
    Route::apiResource('holiday', HolidayController::class);
});
