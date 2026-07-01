<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSystemUserAccount
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user() instanceof User, 403, 'System user account required.');

        return $next($request);
    }
}
