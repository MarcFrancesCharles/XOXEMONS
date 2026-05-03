<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\XuxemonController;
use App\Http\Controllers\FriendController;
use App\Http\Controllers\ChatController;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::group(['middleware' => 'auth:api'], function () {

    // Autenticació i Perfil
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::patch('/user/profile', [AuthController::class, 'updateProfile']);
    Route::delete('/user/account', [AuthController::class, 'deleteAccount']);
    Route::post('/user/daily-reward', [AuthController::class, 'claimDailyReward']);

    // Inventari i Xuxemons
    Route::get('/inventory', [InventoryController::class, 'index']);
    Route::get('/xuxedex', [XuxemonController::class, 'index']);
    Route::post('/xuxemons/{pivot_id}/feed', [XuxemonController::class, 'feed']);
    Route::post('/xuxemons/{pivot_id}/vaccinate', [XuxemonController::class, 'vaccinate']);

    // Admin
    Route::get('/admin/users', [AdminController::class, 'getUsers']);
    Route::post('/admin/give-item', [AdminController::class, 'giveItem']);
    Route::post('/admin/give-xuxemon', [AdminController::class, 'giveRandomXuxemon']);
    Route::get('/admin/settings', [AdminController::class, 'getSettings']);
    Route::post('/admin/settings', [AdminController::class, 'updateSettings']);

    // Amistats
    Route::get('/friends/search', [FriendController::class, 'searchUsers']);
    Route::post('/friends/request', [FriendController::class, 'sendRequest']);
    Route::get('/friends/requests', [FriendController::class, 'getPendingRequests']);
    Route::post('/friends/accept/{id}', [FriendController::class, 'acceptRequest']);
    Route::delete('/friends/reject/{id}', [FriendController::class, 'rejectRequest']);
    Route::get('/friends', [FriendController::class, 'getFriends']);
    Route::delete('/friends/{id}', [FriendController::class, 'removeFriend']);

    // Xat
    Route::get('/chat/{friendId}', [ChatController::class, 'getMessages']);
    Route::post('/chat/{friendId}', [ChatController::class, 'sendMessage']);


});