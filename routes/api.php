<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FileSystemEntryController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
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

Route::post('/login', [AuthController::class, 'login']);



Route::middleware('auth:sanctum')->group(function () {

    Route::get('/logout', [AuthController::class, 'logout']);

    Route::prefix('/users')->group( function () {
        Route::get('/', [UserController::class, 'index']);
        Route::post('/create', [UserController::class, 'create']);
        Route::get('/{user}', [UserController::class, 'show']);
        Route::put('/{user}/update', [UserController::class, 'update']);
        Route::delete('/{user}/delete', [UserController::class, 'destroy']);
        Route::get('/{user}/groups', [UserController::class, 'userGroups']);
        Route::get('/{user}/groups/available', [UserController::class, 'userAvailableGroups']);

    });

    Route::prefix('/groups')->group( function () {
        Route::get('/', [GroupController::class, 'index']);
        Route::post('/create', [GroupController::class, 'store']);
        Route::put('/{group}/update', [GroupController::class, 'update']);
        Route::delete('/{group}/delete', [GroupController::class, 'destroy']);
        Route::get('/{group}/users', [GroupController::class, 'groupUsers']);
        Route::get('/{group}/users/available', [GroupController::class, 'groupAvailableUsers']);
//        Route::middleware('is_admin')->group(function (){
//            Route::post('/{group}/{fileSystemEntry}/link',[GroupController::class,'linkFileSystemEntry']);
//            Route::put('/{group}/{fileSystemEntry}/update-permissions',[GroupController::class,'updateFileSystemEntryPermissions']);
//        });
        Route::post('/{user}/groups/add/{group}', [GroupController::class, 'addUser']);
        Route::delete('/{user}/groups/delete/{group}', [GroupController::class, 'removeUser']);
    });

    Route::prefix('/documents')->group( function () {
        Route::get('/{fileSystemEntry}', [FileSystemEntryController::class, 'index']);
        Route::get('/{fileSystemEntry}/versions', [FileSystemEntryController::class, 'versions']);
        Route::post('/{fileSystemEntry}/versions/add', [FileSystemEntryController::class, 'add_version']);
        Route::get('/media/{media}/download', [FileSystemEntryController::class, 'download']);
        Route::get('/media/{media}/download/last', [FileSystemEntryController::class, 'download_latest']);
        Route::delete('/{fileSystemEntry}/versions/{version}/delete', [FileSystemEntryController::class, 'delete_version']);
        Route::get('/{fileSystemEntry}/show', [FileSystemEntryController::class, 'show']);
        Route::put('/{fileSystemEntry}/update', [FileSystemEntryController::class, 'update']);
        Route::delete('/{fileSystemEntry}/delete', [FileSystemEntryController::class, 'destroy']);
        Route::middleware('is_admin')->group(function(){
            Route::post('/{fileSystemEntry}/manipulate-groups-and-permissions',[FileSystemEntryController::class,'manipulateGroupsAndPermissions']);
            Route::get('/{fileSystemEntry}/get-groups',[FileSystemEntryController::class,'getGroups']);
            Route::get('/{fileSystemEntry}/linkable-groups',[FileSystemEntryController::class,'linkableGroups']);
        });
        Route::get('/go-back/{fileSystemEntry}', [FileSystemEntryController::class, 'goBack']);
        Route::post('/{parent}/create', [FileSystemEntryController::class, 'store']);

    });

    Route::prefix('/categories')->group( function () {
        Route::get('/levels', [CategoryController::class, 'all_levels']);
        Route::get('/last-level', [CategoryController::class, 'last_level']);
        Route::post('/create', [CategoryController::class, 'store']);
    });
});
