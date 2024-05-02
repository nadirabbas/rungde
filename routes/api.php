<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RoomChatController;
use App\Http\Controllers\Api\RoomsController;
use App\Http\Controllers\Api\UsersController;
use App\Http\Controllers\Api\VoiceTokensController;
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

Route::get('/auth/oauth', [AuthController::class, 'oauth']);
Route::get('/auth/callback', [AuthController::class, 'callback']);

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout']);

Route::post('/verify-voice-token', [VoiceTokensController::class, 'verifyVoiceToken']);

Route::middleware('auth')->group(function () {
    Route::get('/room/voice-token', [VoiceTokensController::class, 'requestVoiceToken']);
    Route::get('/me', [UsersController::class, 'me']);
    Route::put('/me/stream', [UsersController::class, 'setStreamId']);

    Route::put('/room/leave', [RoomsController::class, 'leave']);
    Route::put('/room/close', [RoomsController::class, 'close']);
    Route::put('/room/kick', [RoomsController::class, 'kick']);


    Route::put('/room/chat', [RoomChatController::class, 'newChat']);

    Route::get('/rooms/current', [RoomsController::class, 'current']);
    Route::put('/rooms/join', [RoomsController::class, 'join']);
    Route::resource('rooms', RoomsController::class);
});
