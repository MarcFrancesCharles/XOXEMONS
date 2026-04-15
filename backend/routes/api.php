<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\XuxemonController;
use App\Http\Controllers\FriendController;

// Rutes públiques (No cal estar loguejat)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rutes protegides (Cal enviar el token JWT)
Route::group(['middleware' => 'auth:api'], function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/inventory', [InventoryController::class, 'index']);
    Route::get('/xuxedex', [XuxemonController::class, 'index']);
    Route::get('/admin/users', [AdminController::class, 'getUsers']);
    Route::post('/admin/give-item', [AdminController::class, 'giveItem']);
    Route::post('/admin/give-xuxemon', [AdminController::class, 'giveRandomXuxemon']);
    Route::post('/xuxemons/{pivot_id}/feed', [XuxemonController::class, 'feed']);
    Route::post('/xuxemons/{pivot_id}/vaccinate', [XuxemonController::class, 'vaccinate']); 
    Route::post('/user/daily-reward', [AuthController::class, 'claimDailyReward']);
    Route::get('/admin/settings', [AdminController::class, 'getSettings']);
    Route::post('/admin/settings', [AdminController::class, 'updateSettings']);
    Route::get('/friends/search', [FriendController::class, 'searchUsers']);
    Route::post('/friends/request', [FriendController::class, 'sendRequest']);
    Route::get('/friends/requests', [FriendController::class, 'getPendingRequests']);
    Route::post('/friends/accept/{id}', [FriendController::class, 'acceptRequest']);
    Route::delete('/friends/reject/{id}', [FriendController::class, 'rejectRequest']);
    Route::get('/friends', [FriendController::class, 'getFriends']);
    Route::delete('/friends/{id}', [FriendController::class, 'removeFriend']);
    Route::patch('/user/profile', [AuthController::class, 'updateProfile']);
    Route::delete('/user/account', [AuthController::class, 'deleteAccount']);
    Route::get('/chat/{friendId}', [\App\Http\Controllers\ChatController::class, 'getMessages']);
    Route::post('/chat/{friendId}', [\App\Http\Controllers\ChatController::class, 'sendMessage']);
    Route::get('/battle/{friendId}', [\App\Http\Controllers\BattleController::class, 'getBattleData']);
    Route::post('/battle/transfer', [\App\Http\Controllers\BattleController::class, 'transferXuxemon']);

});