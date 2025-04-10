<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DeveloperMiddleware
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
        if(!env("DEVELOPER_LOGIN_ENABLED")) {
            return response()->json([
                "message" => "developer login is lon enabled."
            ]);
        }

        if($request->session()->get("token") !== '12345678' && !env("DeveloperAutoLogin")) {
            return redirect()->route("login.view");
        }
        return $next($request);
    }
}
