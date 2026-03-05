<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\IRacingApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use RuntimeException;

class IRacingOAuthController extends Controller
{
    public function __construct(private readonly IRacingApiService $iracingApiService)
    {
    }

    public function redirect(Request $request)
    {
        if (! $request->user() || $request->user()->role !== 'driver') {
            abort(403);
        }

        $state = Str::random(40);
        $request->session()->put('iracing_oauth_state', $state);

        try {
            $url = $this->iracingApiService->buildAuthorizationUrl();
            $separator = str_contains($url, '?') ? '&' : '?';

            return redirect()->away($url.$separator.http_build_query(['state' => $state]));
        } catch (RuntimeException $exception) {
            return redirect()->route('driver.profile')->withErrors([
                'oauth' => 'OAuth de iRacing no está configurado en .env.',
            ]);
        }
    }

    public function callback(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string'],
            'state' => ['required', 'string'],
        ]);

        if (! $request->user() || $request->user()->role !== 'driver') {
            abort(403);
        }

        $state = (string) $request->session()->pull('iracing_oauth_state', '');
        if (! hash_equals($state, $request->string('state')->toString())) {
            return redirect()->route('driver.profile')->withErrors([
                'oauth' => 'El estado OAuth no es válido.',
            ]);
        }

        try {
            $tokenPayload = $this->iracingApiService->exchangeCodeForToken($request->string('code')->toString());
            $memberInfo = $this->iracingApiService->getWithBearerToken('data/member/info', $tokenPayload['access_token']);
        } catch (RuntimeException $exception) {
            return redirect()->route('driver.profile')->withErrors([
                'oauth' => 'No se pudo completar la vinculación OAuth de iRacing.',
            ]);
        }

        $custId = (string) (data_get($memberInfo, 'cust_id') ?? data_get($memberInfo, 'member_info.cust_id'));
        $displayName = (string) (data_get($memberInfo, 'display_name') ?? data_get($memberInfo, 'member_info.display_name') ?? 'Pilot');

        if ($custId === '') {
            return redirect()->route('driver.profile')->withErrors([
                'oauth' => 'iRacing no devolvió un cust_id válido.',
            ]);
        }

        $user = $request->user();

        $user->forceFill([
            'name' => $displayName ?: $user->name,
            'iracing_customer_id' => $custId,
            'access_token' => $tokenPayload['access_token'],
            'refresh_token' => $tokenPayload['refresh_token'] ?? $user->refresh_token,
            'token_expires_at' => $this->iracingApiService->accessTokenExpiresAt($tokenPayload),
            'iracing_linked' => true,
        ])->save();

        return redirect()->route('driver.profile')->with('status', 'Cuenta de iRacing vinculada correctamente.');
    }
}
