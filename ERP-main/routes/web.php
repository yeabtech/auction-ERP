<?php

use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Auctioneer\AuctionControl;
use App\Livewire\Bidder\Browse;
use App\Livewire\Bidder\LiveBidding;
use App\Models\Lot;
use Illuminate\Support\Facades\Route;

// ─── Public Home ─────────────────────────────────────────────────────────────
Route::get('/', Browse::class)->name('home');

// ─── Auth (Livewire's built-in or custom) ────────────────────────────────────
Route::view('/login', 'auth.login')->name('login');
Route::view('/register', 'auth.register')->name('register');
Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout');

// ─── Public Bidder Browsing ───────────────────────────────────────────────────
Route::get('/auctions', Browse::class)->name('bidder.browse');
Route::get('/auctions/{auction}', function (\App\Models\Auction $auction) {
    // Show the first active lot for demo purposes
    $lot = $auction->lots()->where('status', 'active')->first()
           ?? $auction->lots()->first();
    if (!$lot) abort(404, 'No lots found in this auction.');
    return redirect()->route('bidder.lot', $lot);
})->name('bidder.auction');
Route::get('/lots/{lot}/bid', LiveBidding::class)->name('bidder.lot');

// ─── Bidder Portal (auth required) ───────────────────────────────────────────
Route::middleware(['auth'])->prefix('bidder')->name('bidder.')->group(function () {
    Route::view('/dashboard', 'bidder.dashboard')->name('dashboard');
});

// ─── Seller / Vendor Portal (auth & role:seller required) ───────────────────
Route::middleware(['auth', 'role:seller,super_admin,auction_manager'])
    ->prefix('seller')
    ->name('seller.')
    ->group(function () {
        Route::get('/assets', \App\Livewire\Seller\Assets::class)->name('assets');
        Route::get('/auctions', \App\Livewire\Seller\Auctions::class)->name('auctions');
    });

// ─── Admin Portal ─────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:super_admin,auction_manager,finance_officer,auditor'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', AdminDashboard::class)->name('dashboard');
        Route::view('/users', 'admin.users')->name('users');
        Route::view('/auctions', 'admin.auctions')->name('auctions');
        Route::view('/lots', 'admin.lots')->name('lots');
        Route::view('/kyc', 'admin.kyc')->name('kyc');
        Route::view('/finance', 'admin.finance')->name('finance');
        Route::view('/reports', 'admin.reports')->name('reports');
        Route::view('/audit', 'admin.audit')->name('audit');
        Route::view('/settings', 'admin.settings')->name('settings');
    });

// ─── Auctioneer Portal ────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:auctioneer,super_admin,auction_manager'])
    ->prefix('auctioneer')
    ->name('auctioneer.')
    ->group(function () {
        Route::get('/auction/{auction}/control', AuctionControl::class)->name('control');
    });
