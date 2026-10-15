<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PushSubscriptionController extends Controller
{
	public function store(Request $request): Response
	{
		$validated = $request->validate([
			'endpoint' => ['required', 'url', 'max:500'],
			'keys.p256dh' => ['required', 'string'],
			'keys.auth' => ['required', 'string'],
			'contentEncoding' => ['nullable', 'string', 'in:aesgcm,aes128gcm'],
		]);

		$request->user()->updatePushSubscription(
			$validated['endpoint'],
			$validated['keys']['p256dh'],
			$validated['keys']['auth'],
			$validated['contentEncoding'] ?? 'aesgcm',
		);

		return response()->noContent();
	}

	public function destroy(Request $request): Response
	{
		$validated = $request->validate(['endpoint' => ['required', 'string']]);

		$request->user()->deletePushSubscription($validated['endpoint']);

		return response()->noContent();
	}
}
