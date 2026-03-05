<?php

namespace App\Http\Middleware;

use App\Services\IRacingApiService;
use Closure;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class RefreshIRacingToken
{
    public function __construct(private readonly IRacingApiService $iracingApiService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== 'driver' || ! $user->iracing_linked || empty($user->refresh_token)) {
            return $next($request);
        }

        if (! $user->token_expires_at || now()->lt($user->token_expires_at->copy()->subMinute())) {
            return $next($request);
        }

        try {
            $tokenPayload = $this->iracingApiService->refreshAccessToken($user->refresh_token);

            $user->forceFill([
                'access_token' => $tokenPayload['access_token'],
                'refresh_token' => $tokenPayload['refresh_token'] ?? $user->refresh_token,
                'token_expires_at' => $this->iracingApiService->accessTokenExpiresAt($tokenPayload),
            ])->save();
        } catch (RuntimeException $exception) {
            report($exception);
        }

        return $next($request);
    }
}
