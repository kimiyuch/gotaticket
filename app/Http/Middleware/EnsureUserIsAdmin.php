<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lets admins into the admin area; to everyone else (guests included) it answers 404, so the area stays invisible.
 */
class EnsureUserIsAdmin
{
	/**
	 * Handle an incoming request.
	 *
	 * @param Closure(Request): (Response) $next
	 */
	public function handle(Request $request, Closure $next): Response
	{
		abort_unless($request->user()?->can('admin'), 404);

		return $next($request);
	}
}
