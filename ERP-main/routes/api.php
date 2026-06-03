<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AuctionController;
use App\Http\Controllers\Api\BidController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [AuthController::class, 'login'])->name('api.login');
Route::get('/auctions', [AuctionController::class, 'index'])->name('api.auctions.index');
Route::get('/auctions/{auction}', [AuctionController::class, 'show'])->name('api.auctions.show');

// Protected routes (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user'])->name('api.user');
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::post('/bids', [BidController::class, 'placeBid'])->name('api.bids.place');
});
