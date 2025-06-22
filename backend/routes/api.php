<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\KanbanController;
use Illuminate\Support\Facades\Route;

// Authentication routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // Kanban routes
    Route::get('/kanban', [KanbanController::class, 'index']);
    Route::post('/kanban/tasks', [KanbanController::class, 'store']);
    Route::put('/kanban/tasks/{task}', [KanbanController::class, 'update']);
    Route::delete('/kanban/tasks/{task}', [KanbanController::class, 'destroy']);
    Route::put('/kanban/sync', [KanbanController::class, 'sync']);
}); 