<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Interfaces\Http\Api\V1\Auth\Controllers\Authenticate\AuthenticateApiController;

Route::post('login', AuthenticateApiController::class)->name('login');
