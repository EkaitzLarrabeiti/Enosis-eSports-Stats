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

        // Inicio OAuth (PKCE): guardamos verifier+state en sesión y redirigimos a iRacing.
        $state = Str::random(40);
        $codeVerifier = $this->iracingApiService->createPkceCodeVerifier();
        $codeChallenge = $this->iracingApiService->createPkceCodeChallenge($codeVerifier);

        $request->session()->put('iracing_oauth_state', $state);
        $request->session()->put('iracing_oauth_code_verifier', $codeVerifier);

        try {
            $url = $this->iracingApiService->buildAuthorizationUrl($state, $codeChallenge, 'S256');

            return redirect()->away($url);
        } catch (RuntimeException $exception) {
            return redirect()->route('driver.profile')->withErrors([
                'oauth' => 'OAuth de iRacing no esta configurado en .env.',
            ]);
        }
    }

    public function callback(Request $request)
    {
        // Callback OAuth: validamos state, intercambiamos code por token y pedimos member info.
        if ($request->filled('error')) {
            $description = $request->string('error_description')->toString();
            $message = $description !== '' ? $description : 'iRacing devolvio un error de OAuth.';

            return redirect()->route('driver.profile')->withErrors([
                'oauth' => $message,
            ]);
        }

        $request->validate([
            'code' => ['required', 'string'],
            'state' => ['required', 'string'],
        ]);

        if (! $request->user() || $request->user()->role !== 'driver') {
            abort(403);
        }

        $state = (string) $request->session()->pull('iracing_oauth_state', '');
        $codeVerifier = (string) $request->session()->pull('iracing_oauth_code_verifier', '');

        if (! hash_equals($state, $request->string('state')->toString())) {
            return redirect()->route('driver.profile')->withErrors([
                'oauth' => 'El estado OAuth no es valido.',
            ]);
        }

        if ($codeVerifier === '') {
            return redirect()->route('driver.profile')->withErrors([
                'oauth' => 'No se encontro el PKCE verifier en la sesion.',
            ]);
        }

        try {
            // El intercambio de token + member info nos da el cust_id para vincular la cuenta.
            $tokenPayload = $this->iracingApiService->exchangeCodeForToken($request->string('code')->toString(), $codeVerifier);
            $memberInfo = $this->iracingApiService->getWithBearerToken('data/member/info', $tokenPayload['access_token']);
        } catch (RuntimeException $exception) {
            report($exception);

            $message = 'No se pudo completar la vinculacion OAuth de iRacing.';
            if (config('app.debug')) {
                $message .= ' Detalle: '.$exception->getMessage();
            }

            return redirect()->route('driver.profile')->withErrors([
                'oauth' => $message,
            ]);
        }

        $custId = (string) (data_get($memberInfo, 'cust_id') ?? data_get($memberInfo, 'member_info.cust_id'));
        $displayName = (string) (data_get($memberInfo, 'display_name') ?? data_get($memberInfo, 'member_info.display_name') ?? 'Pilot');

        if ($custId === '') {
            return redirect()->route('driver.profile')->withErrors([
                'oauth' => 'iRacing no devolvio un cust_id valido.',
            ]);
        }

        $user = $request->user();

        // Guardamos la vinculación + tokens para las llamadas del dashboard.
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

    public function unlink(Request $request)
    {
        if (! $request->user() || $request->user()->role !== 'driver') {
            abort(403);
        }

        // Limpiamos la vinculación + tokens cuando el usuario desvincula iRacing.
        $request->user()->forceFill([
            'iracing_linked' => false,
            'iracing_customer_id' => null,
            'access_token' => null,
            'refresh_token' => null,
            'token_expires_at' => null,
        ])->save();

        return redirect()->route('driver.profile')->with('status', 'Cuenta de iRacing desvinculada.');
    }
}
