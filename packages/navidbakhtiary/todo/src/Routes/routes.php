<?php

use Illuminate\Support\Facades\Route;
use NavidBakhtiary\ToDo\Controllers\LabelController;
use NavidBakhtiary\ToDo\Controllers\TaskController;
use NavidBakhtiary\ToDo\Controllers\TaskLabelController;

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
        Route::prefix('label')->group(function () {
            Route::get('/', [LabelController::class, 'index']);
            Route::post('/add', [LabelController::class, 'store']);
        });
        Route::prefix('task')->group(function () {
            Route::get('/', [TaskController::class, 'index']);
            Route::get('/{id}', [TaskController::class, 'details']);
            Route::post('/add', [TaskController::class, 'store']);
            Route::post('/edit', [TaskController::class, 'update']);
            Route::prefix('status')->group(function () {
                Route::post('/switch', [TaskController::class, 'statusSwitching']);
            });
            Route::prefix('label')->group(function () {
                Route::post('/add', [TaskLabelController::class, 'store']);
            });
        });
    });
});
