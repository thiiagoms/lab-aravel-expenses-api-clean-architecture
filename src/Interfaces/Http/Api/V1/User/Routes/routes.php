<?php

use Illuminate\Support\Facades\Route;
use Src\Interfaces\Http\Api\V1\User\Controllers\Register\ConfirmEmailApiController;
use Src\Interfaces\Http\Api\V1\User\Controllers\Register\RegisterUserApiController;

/**
 * |--------------------
 * | Public routes
 * |--------------------
 */
Route::post('register', RegisterUserApiController::class)->name('register');

Route::get('email-confirmation', ConfirmEmailApiController::class)
    ->name('email-confirmation')
    ->middleware('signed');

/**
 * |--------------------
 * | Protected routes
 * |--------------------
 */
