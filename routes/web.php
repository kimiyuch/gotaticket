<?php

use App\Http\Controllers\Auth\MagicLinkController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

Route::middleware('guest')->group(function () {
    Route::livewire('/login', 'pages::login')->name('login');
    Route::get('/login/{user}', MagicLinkController::class)->middleware('signed')->name('login.magic');
});

Route::middleware('auth')->group(function () {
    Route::livewire('/meine-tickets', 'pages::dashboard')->name('dashboard');
    Route::livewire('/ich-han-eis', 'pages::offers.create')->name('offers.create');

    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('home');
    })->name('logout');
});
