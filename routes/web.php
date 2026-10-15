<?php

use App\Http\Controllers\PushSubscriptionController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');
Route::livewire('/ich-bruuch-eis', 'pages::offers.index')->name('offers.index');

Route::middleware('auth')->group(function () {
	Route::livewire('/meine-tickets', 'pages::dashboard')->name('dashboard');
	Route::livewire('/profil', 'pages::profile')->name('profile');
	Route::redirect('/einstellungen', '/profil', 301);

	Route::post('/push-subscriptions', [PushSubscriptionController::class, 'store'])->name('push-subscriptions.store');
	Route::delete('/push-subscriptions', [PushSubscriptionController::class, 'destroy'])->name('push-subscriptions.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
	Route::livewire('/ich-han-eis', 'pages::offers.create')->name('offers.create');
});
