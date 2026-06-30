<?php

namespace App\Http\Middleware;

use App\Services\VisitorContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ResolveVisitorContext
{
    public function __construct(private VisitorContext $resolver) {}

    public function handle(Request $request, Closure $next): Response
    {
        $context = $this->resolver->resolve($request);
        $request->attributes->set('visitor_context', $context);
        View::share('visitorContext', $context);

        if (in_array($request->route('locale'), ['fr', 'en'], true)) {
            Cookie::queue('visitor_locale', $request->route('locale'), config('localization.cookie_minutes'));
        }

        return $next($request);
    }
}
