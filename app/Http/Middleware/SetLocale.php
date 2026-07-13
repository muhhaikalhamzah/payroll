<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $locale = auth()->user()->preferred_locale ?? session('locale', config('app.locale'));
            session(['locale' => $locale]);
        } else {
            $locale = session('locale', config('app.locale'));
        }

        \Illuminate\Support\Facades\App::setLocale($locale);

        return $next($request);
    }
}
