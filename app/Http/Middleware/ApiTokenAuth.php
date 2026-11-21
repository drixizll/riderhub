<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $bearer = $request->bearerToken();

        if (! is_string($bearer) || trim($bearer) === '') {
            return response()->json(['message' => 'Authentication token required.'], 401);
        }

        $user = User::where('api_token', hash('sha256', trim($bearer)))->first();

        if (! $user) {
            return response()->json(['message' => 'Invalid or expired API token.'], 401);
        }

        $request->setUserResolver(static fn () => $user);

        return $next($request);
    }
}
