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

Route::middleware(['auth', 'can:admin'])->prefix('admin')->name('admin.')->group(function () {
	Route::livewire('/', 'pages::admin.index')->name('index');
	Route::livewire('/matches', 'pages::admin.fixtures.index')->name('fixtures.index');
	Route::livewire('/matches/neu', 'pages::admin.fixtures.form')->name('fixtures.create');
	Route::livewire('/matches/{fixture}', 'pages::admin.fixtures.form')->name('fixtures.edit');
	Route::livewire('/sektoren', 'pages::admin.sectors.index')->name('sectors.index');
	Route::livewire('/sektoren/neu', 'pages::admin.sectors.form')->name('sectors.create');
	Route::livewire('/sektoren/{sector}', 'pages::admin.sectors.form')->name('sectors.edit');
	Route::livewire('/fans', 'pages::admin.users')->name('users');
	Route::livewire('/uebergaben', 'pages::admin.claims')->name('claims');
});
