<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
	/**
	 * Register any application services.
	 */
	public function register(): void
	{
		//
	}

	/**
	 * Bootstrap any application services.
	 */
	public function boot(): void
	{
		/*
		 * The stylesheet tag follows right after; the extra preload only triggers
		 * "preloaded but not used" warnings after wire:navigate page swaps.
		 */
		Vite::usePreloadTagAttributes(fn (string $src, string $url): array|false => str_ends_with($url, '.css') ? false : []);
	}
}
