<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RoomsController;
use App\Http\Controllers\Api\UsersController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/logout', [AuthController::class, 'logout']);

Route::middleware('auth')->group(function () {
    Route::get('/me', [UsersController::class, 'me']);

    Route::get('/rooms/current', [RoomsController::class, 'current']);
    Route::put('/rooms/join', [RoomsController::class, 'join']);
    Route::resource('rooms', RoomsController::class);
});
