<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Rutes públiques (No cal estar loguejat)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rutes protegides (Cal enviar el token JWT)
Route::group(['middleware' => 'auth:api'], function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});