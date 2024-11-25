<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Carbon\Carbon;
use ApiStatus;
use Illuminate\Support\Facades\Log;

class SanctumTokenValidator
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
        $token = $request->bearerToken();
        $badRequestStatus = ApiStatus::API_STATUS_UN_AUTHORIZED;
        if (!$token) {
            return response()->json([
                'status' => ApiStatus::FAILURE,
                'message' => ApiStatus::UN_AUTHORIZE,
                'status_code' => $badRequestStatus
            ], $badRequestStatus);
        }
        $accessToken = PersonalAccessToken::findToken($token);
        if (!$accessToken) {
            return response()->json([
                'status' => ApiStatus::FAILURE,
                'message' => ApiStatus::TOKEN_INVALID,
                'status_code' => $badRequestStatus
            ], $badRequestStatus);
        }
        $expiry = env('SANCTUM_TOKEN_EXPIRY_IN_MINUTES',60);
        if ($accessToken->created_at->addMinutes($expiry) < Carbon::now()) {
            return response()->json([
                'status' => ApiStatus::FAILURE,
                'message' => ApiStatus::TOKEN_EXPIRED,
                'status_code' => $badRequestStatus
            ], $badRequestStatus);
        }
        $user = $accessToken->tokenable;
        $request->setUserResolver(fn() => $user);
        return $next($request);
    }
}
