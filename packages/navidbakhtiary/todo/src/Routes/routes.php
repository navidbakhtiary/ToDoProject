<?php

use Illuminate\Support\Facades\Route;
use NavidBakhtiary\ToDo\Controllers\LabelController;
use NavidBakhtiary\ToDo\Controllers\TaskController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('todo')->group(function () {
        Route::prefix('labels')->group(function () {
            Route::post('/add', [LabelController::class, 'store']);
        });
        Route::prefix('tasks')->group(function () {
            Route::post('/add', [TaskController::class, 'store']);
            Route::post('/edit', [TaskController::class, 'update']);
        });
    });
});
