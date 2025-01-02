<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ListController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\ToDoController;
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

Route::post('login', [AdminController::class, 'login'])->middleware('check.api');

Route::group(['middleware' => ['auth:admin-api', 'check.api']], function () {
    Route::get('admin', [AdminController::class, 'index']);
    Route::post('logout', [AdminController::class, 'logout']);

    Route::get('load-menu', [ListController::class, 'menu']);

    // Begin: Admin Profile
    Route::prefix('profile')->group(function () {
        Route::post('edit-profile', [AdminController::class, 'updateProfile']);
        Route::post('change-password', [AdminController::class, 'changePassword']);
    });
    // End: Admin Profile

    // Begin:: To-Do
    Route::resource('to-do', ToDoController::class);
    Route::prefix('to-do')->group(function () {
        Route::post('delete-all', [ToDoController::class, 'deleteAll']);
    });
    // End:: To-Do

    // Begin:: Tag
    Route::resource('tag', TagController::class);

    Route::get('all-tags', [TagController::class, 'allTags']);
    Route::prefix('tag')->group(function () {
        Route::post('delete-all', [TagController::class, 'deleteAll']);
    });
    // End:: Tag

});

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});
