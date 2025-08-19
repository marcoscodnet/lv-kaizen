<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ContentSecurityPolicy
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $csp = "default-src 'self'; ".
            "script-src 'self' 'unsafe-inline' https://code.jquery.com https://cdnjs.cloudflare.com; ".
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com; ".
            "img-src 'self' data:; ".
            "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com;";


        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
