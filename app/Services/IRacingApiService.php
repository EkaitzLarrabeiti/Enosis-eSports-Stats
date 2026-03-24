<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class IRacingApiService
{
    public function buildAuthorizationUrl(string $state, ?string $codeChallenge = null, string $codeChallengeMethod = 'S256'): string
    {
        $config = config('services.iracing');

        $this->ensureConfigValues([
            'client_id' => $config['client_id'] ?? null,
            'redirect_uri' => $config['redirect_uri'] ?? null,
        ]);

        $query = [
            'client_id' => $config['client_id'],
            'redirect_uri' => $config['redirect_uri'],
            'response_type' => 'code',
            'scope' => $config['scope'] ?? 'iracing.auth',
            'state' => $state,
        ];

        if (! empty($codeChallenge)) {
            $query['code_challenge'] = $codeChallenge;
            $query['code_challenge_method'] = $codeChallengeMethod;
        }

        return rtrim($config['oauth_base_url'], '/').'/oauth2/authorize?'.http_build_query($query);
    }

    public function createPkceCodeVerifier(int $length = 64): string
    {
        if ($length < 43 || $length > 128) {
            throw new RuntimeException('PKCE code verifier length must be between 43 and 128.');
        }

        $allowed = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890-._~';
        $maxIndex = strlen($allowed) - 1;
        $verifier = '';

        for ($i = 0; $i < $length; $i++) {
            $verifier .= $allowed[random_int(0, $maxIndex)];
        }

        return $verifier;
    }

    public function createPkceCodeChallenge(string $codeVerifier): string
    {
        if (! preg_match('/^[A-Za-z0-9\\-._~]{43,128}$/', $codeVerifier)) {
            throw new RuntimeException('PKCE code verifier format is invalid.');
        }

        $hash = hash('sha256', $codeVerifier, true);

        return rtrim(strtr(base64_encode($hash), '+/', '-_'), '=');
    }

    public function exchangeCodeForToken(string $code, ?string $codeVerifier = null): array
    {
        $config = config('services.iracing');

        $this->ensureConfigValues([
            'client_id' => $config['client_id'] ?? null,
            'redirect_uri' => $config['redirect_uri'] ?? null,
        ]);

        $payload = [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $config['redirect_uri'],
            'client_id' => $config['client_id'],
        ];

        if (! empty($config['client_secret'])) {
            $payload['client_secret'] = $this->maskSecret($config['client_secret'], $config['client_id']);
        }

        if (! empty($codeVerifier)) {
            $payload['code_verifier'] = $codeVerifier;
        }

        $response = Http::asForm()->post(
            rtrim($config['oauth_base_url'], '/').'/oauth2/token',
            $payload
        );

        $data = $this->decodeResponse($response);

        $this->assertTokenPayload($data);

        return $data;
    }

    public function refreshAccessToken(string $refreshToken): array
    {
        $config = config('services.iracing');

        $this->ensureConfigValues([
            'client_id' => $config['client_id'] ?? null,
        ]);

        $payload = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => $config['client_id'],
        ];

        if (! empty($config['client_secret'])) {
            $payload['client_secret'] = $this->maskSecret($config['client_secret'], $config['client_id']);
        }

        $response = Http::asForm()->post(
            rtrim($config['oauth_base_url'], '/').'/oauth2/token',
            $payload
        );

        $data = $this->decodeResponse($response);

        $this->assertTokenPayload($data);

        return $data;
    }

    public function requestServerToken(): array
    {
        $config = config('services.iracing');
        $pwClientId = $config['pw_client_id'] ?? $config['client_id'] ?? null;
        $pwClientSecret = $config['pw_client_secret'] ?? $config['client_secret'] ?? null;
        $pwScope = $config['pw_scope'] ?? $config['scope'] ?? 'iracing.auth';

        $this->ensureConfigValues([
            'pw_client_id' => $pwClientId,
            'pw_client_secret' => $pwClientSecret,
            'server_username' => $config['server_username'] ?? null,
            'server_password' => $config['server_password'] ?? null,
        ]);

        $response = Http::asForm()->post(
            rtrim($config['oauth_base_url'], '/').'/oauth2/token',
            [
                'grant_type' => 'password_limited',
                'username' => $config['server_username'],
                'password' => $this->maskSecret($config['server_password'], $config['server_username']),
                'client_id' => $pwClientId,
                'client_secret' => $this->maskSecret($pwClientSecret, $pwClientId),
                'scope' => $pwScope,
            ]
        );

        $data = $this->decodeResponse($response);

        $this->assertTokenPayload($data);

        return $data;
    }

    public function maskSecret(string $secret, string $id): string
    {
        $normalizedId = strtolower(trim($id));
        $combined = $secret.$normalizedId;

        return base64_encode(hash('sha256', $combined, true));
    }

    public function getWithBearerToken(string $endpoint, string $accessToken, array $query = []): array
    {
        $config = config('services.iracing');
        $url = str_starts_with($endpoint, 'http')
            ? $endpoint
            : rtrim($config['api_base_url'], '/').'/'.ltrim($endpoint, '/');

        $response = Http::withToken($accessToken)->get($url, $query);
        $payload = $this->decodeResponse($response);

        return $this->resolveSignedUrlPayload($payload, $accessToken);
    }

    public function getForUser(User $user, string $endpoint, array $query = []): array
    {
        if (empty($user->access_token)) {
            throw new RuntimeException('User has no iRacing access token.');
        }

        return $this->getWithBearerToken($endpoint, $user->access_token, $query);
    }

    public function accessTokenExpiresAt(array $tokenPayload): Carbon
    {
        $ttl = (int) Arr::get($tokenPayload, 'expires_in', 600);

        return now()->addSeconds($ttl);
    }

    private function ensureConfigValues(array $values): void
    {
        $missing = [];

        foreach ($values as $key => $value) {
            if (empty($value)) {
                $missing[] = $key;
            }
        }

        if ($missing !== []) {
            throw new RuntimeException('Missing iRacing config values: '.implode(', ', $missing));
        }
    }

    private function decodeResponse(Response $response): array
    {
        if (! $response->successful()) {
            $body = $response->body();
            $summary = $body !== '' ? substr($body, 0, 500) : 'empty response body';

            throw new RuntimeException('iRacing request failed with status '.$response->status().'. Response: '.$summary);
        }

        $json = $response->json();

        if (! is_array($json)) {
            $body = $response->body();
            $summary = $body !== '' ? substr($body, 0, 500) : 'empty response body';
            $contentType = $response->header('Content-Type') ?? 'unknown';

            throw new RuntimeException('iRacing response is not valid JSON. Content-Type: '.$contentType.'. Body: '.$summary);
        }

        return $json;
    }

    private function resolveSignedUrlPayload(array $payload, string $accessToken): array
    {
        $signedUrl = Arr::get($payload, 'link');

        if (! is_string($signedUrl) || $signedUrl === '') {
            return $payload;
        }

        $response = Http::get($signedUrl);

        return $this->decodeResponse($response);
    }

    private function assertTokenPayload(array $payload): void
    {
        if (empty($payload['access_token'])) {
            throw new RuntimeException('iRacing token response does not include access_token.');
        }
    }
}
