<?php

use Illuminate\Support\Facades\Route;
use Src\Interfaces\Http\Api\V1\User\Controllers\Profile\ProfileApiController;
use Src\Interfaces\Http\Api\V1\User\Controllers\Register\ConfirmEmailApiController;
use Src\Interfaces\Http\Api\V1\User\Controllers\Register\RegisterUserApiController;
use Src\Interfaces\Http\Api\V1\User\Controllers\Update\UpdateUserApiController;

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
Route::middleware(['auth:api'])->group(function (): void {

    Route::prefix('user')->name('user.')->group(function (): void {

        Route::prefix('profile')->name('profile.')->group(function (): void {
            Route::get('', ProfileApiController::class)->name('get');

            Route::controller(UpdateUserApiController::class)
                ->name('Update.')
                ->group(function (): void {
                    Route::patch('', 'Update')->name('patch');
                    Route::put('', 'Update')->name('put');
                });
        });
    });
});
