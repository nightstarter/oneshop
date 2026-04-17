<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class SetApplicationLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = (string) config('app.locale', 'cs');

        app()->setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}