<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MovieController;
use App\Http\Controllers\Api\WatchedController;
use App\Http\Controllers\Api\WatchlistController;
use App\Http\Controllers\Api\RecommendationController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::middleware('auth:api')->get('me', [AuthController::class, 'me']);
});

Route::get('movies', [MovieController::class, 'index']);
Route::get('movies/{id}', [MovieController::class, 'show'])->whereNumber('id');
Route::post('movies/recommend', RecommendationController::class)->middleware('throttle:10,1');

Route::middleware('auth:api')->group(function () {
    Route::get('watchlist', [WatchlistController::class, 'index']);
    Route::post('watchlist', [WatchlistController::class, 'store']);
    Route::delete('watchlist/{movieId}', [WatchlistController::class, 'destroy'])->whereNumber('movieId');

    Route::get('watched', [WatchedController::class, 'index']);
    Route::post('watched', [WatchedController::class, 'store']);
});