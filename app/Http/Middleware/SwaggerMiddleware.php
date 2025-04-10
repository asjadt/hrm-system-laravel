<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SwaggerMiddleware
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
        if(!env("SWAGGER_ENABLED")) {
            return redirect("/");
        }

        if($request->session()->get("token") !== '12345678' && !env("SwaggerAutoLogin")) {
            return redirect()->route("login.view");

        }
        return $next($request);
    }
}
