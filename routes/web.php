<?php

use App\Http\Controllers\Auth\MagicLinkController;
use App\Http\Controllers\PushSubscriptionController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');
Route::livewire('/ich-bruuch-eis', 'pages::offers.index')->name('offers.index');

Route::middleware('guest')->group(function () {
	Route::livewire('/login', 'pages::login')->name('login');
	Route::get('/login/{user}', MagicLinkController::class)->middleware('signed')->name('login.magic');
});

Route::middleware('auth')->group(function () {
	Route::livewire('/meine-tickets', 'pages::dashboard')->name('dashboard');
	Route::livewire('/ich-han-eis', 'pages::offers.create')->name('offers.create');
	Route::livewire('/einstellungen', 'pages::settings')->name('settings');

	Route::post('/push-subscriptions', [PushSubscriptionController::class, 'store'])->name('push-subscriptions.store');
	Route::delete('/push-subscriptions', [PushSubscriptionController::class, 'destroy'])->name('push-subscriptions.destroy');

	Route::post('/logout', function () {
		Auth::logout();
		request()->session()->invalidate();
		request()->session()->regenerateToken();

		return redirect()->route('home')->with('toast', 'Du bist abgemeldet.');
	})->name('logout');
});
