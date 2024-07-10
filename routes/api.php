<?php

use App\Http\Controllers\Api\AddFriendController;
use App\Http\Controllers\Api\AddGroupController;
use App\Http\Controllers\Api\MessageFileController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\VideoCallController;
use App\Models\AddFriend;
use App\Models\MessageFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', [UserController::class, 'login']);
Route::post('/register', [UserController::class, 'create']);



Route::middleware('jwt.auth')->group(function () {
    Route::post('/logout', [UserController::class, 'logout']);

    Route::group(['prefix' => 'user'], function () {
        Route::get('/', [UserController::class, 'me']);
        Route::get('/addfriends', [UserController::class, 'addfriendNotAccess']);
        Route::get('/addgroups', [UserController::class, 'addgroupNotAccess']);
        Route::get('/groups', [UserController::class, 'userGroups']);
        Route::get('/friends', [UserController::class, 'userFriends']);
        Route::get('/friend-options', [UserController::class, 'userFriendOptions']);
    });
    Route::group(['prefix' => 'users'], function () {
        Route::get('/search', [UserController::class, 'search']);
    });

    Route::group(['prefix' => 'addfriend'], function () {
        Route::post('/', [AddFriendController::class, 'create']);
        Route::post('/unfriend/{friend_id}', [AddFriendController::class, 'unFriend']);
        Route::put('/{id}', [AddFriendController::class, 'access']);
        Route::delete('/{id}', [AddFriendController::class, 'refuse']);
    });
    Route::group(['prefix' => 'addgroup'], function () {
        Route::post('/', [AddGroupController::class, 'create']);
        Route::put('/{id}', [AddGroupController::class, 'access']);
        Route::delete('/{id}', [AddGroupController::class, 'refuse']);
    });

    Route::group(['prefix' => 'rooms'], function () {
        Route::get('/', [RoomController::class, 'index']);
        Route::post('/', [RoomController::class, 'create']);
        Route::get('/{room_id}/messages', [RoomController::class, 'messages']);
        Route::post('/{room_id}/message', [UserController::class, 'message']);
        Route::put('/{room_id}', [RoomController::class, 'outroom']);
    });

    Route::group(['prefix' => 'calls'], function () {
        Route::post('/call-video', [VideoCallController::class, 'callVideo']);
        Route::post('/access-call-video', [VideoCallController::class, 'accessVideoCall']);
        Route::post('/candidate', [VideoCallController::class, 'candidateIce']);
    });

    Route::post('/upload', [MessageFileController::class, 'upload']);
});
