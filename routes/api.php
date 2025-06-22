<?php

opcache_reset();

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\KanbanController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public authentication routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/kanban', [KanbanController::class, 'index']);
    Route::put('/kanban/sync', [KanbanController::class, 'sync']);
    Route::post('/kanban/tasks', [KanbanController::class, 'storeTask']);
    Route::put('/kanban/tasks/{task}', [KanbanController::class, 'updateTask']);
    Route::delete('/kanban/tasks/{task}', [KanbanController::class, 'destroyTask']);
}); 