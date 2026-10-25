<?php

namespace App\Sales\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** The Sales API only ever speaks JSON: a client that forgot the Accept header still gets {code,message,errors}, never a redirect to a login page. */
class ForceJson
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Accept', 'application/json');

        return $next($request);
    }
}
