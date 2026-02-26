<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LinkController;
use App\Http\Controllers\Api\LinkTagController;
use App\Http\Controllers\Api\TagController;
use Illuminate\Support\Facades\Route;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::patch('links/{link}/favourite', [LinkController::class, 'toggleFavourite']);

    // Tags List
    Route::get('/tags', [TagController::class, 'index']);

    // Attach/ Detach tags to links
    Route::post('/links/{link}/tags', [LinkTagController::class, 'store']);
    Route::delete('/links/{link}/tags/{tag}', [LinkTagController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('links', LinkController::class);
});
