<?php

namespace App\Http\Middleware;

use App\Models\BusinessSubscription;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;

class BusinessSubscriptionChecker
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

        $user = auth()->user();
        $business = $user->business;

        if ($user && $user->business) {
            $business = $user->business;


            if (!$business->is_active) {
                return response()->json(["message" => "Business is not active."], 401);
            }

            if (!$business->is_subscribed) {
                return response()->json(["message" => "Your subscription has ended."], 401);
            }

          
        }

        return $next($request);
    }
}
