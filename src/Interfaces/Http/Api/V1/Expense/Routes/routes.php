<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Interfaces\Http\Api\V1\Expense\Controllers\ExpenseApiController;

Route::controller(ExpenseApiController::class)->middleware(['auth:api'])->group(function (): void {
    Route::get('', 'index')->name('index');
    Route::get('/{expense}', 'show')->name('show');
    Route::post('', 'store')->name('store');
    Route::patch('{expense}', 'update')->name('update');
    Route::put('{expense}', 'update')->name('update');
    Route::delete('{expense}', 'destroy')->name('destroy');
});
