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
    public function buildAuthorizationUrl(): string
    {
        $config = config('services.iracing');

        $this->ensureConfigValues([
            'client_id' => $config['client_id'] ?? null,
            'redirect_uri' => $config['redirect_uri'] ?? null,
        ]);

        return rtrim($config['oauth_base_url'], '/').'/oauth2/authorize?'.http_build_query([
            'client_id' => $config['client_id'],
            'redirect_uri' => $config['redirect_uri'],
            'response_type' => 'code',
            'scope' => 'iracing.auth',
        ]);
    }

    public function exchangeCodeForToken(string $code): array
    {
        $config = config('services.iracing');

        $this->ensureConfigValues([
            'client_id' => $config['client_id'] ?? null,
            'client_secret' => $config['client_secret'] ?? null,
            'redirect_uri' => $config['redirect_uri'] ?? null,
        ]);

        $response = Http::asForm()->post(
            rtrim($config['oauth_base_url'], '/').'/oauth2/token',
            [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $config['redirect_uri'],
                'client_id' => $config['client_id'],
                'client_secret' => $this->maskSecret($config['client_secret'], $config['client_id']),
            ]
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
            'client_secret' => $config['client_secret'] ?? null,
        ]);

        $response = Http::asForm()->post(
            rtrim($config['oauth_base_url'], '/').'/oauth2/token',
            [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
                'client_id' => $config['client_id'],
                'client_secret' => $this->maskSecret($config['client_secret'], $config['client_id']),
            ]
        );

        $data = $this->decodeResponse($response);

        $this->assertTokenPayload($data);

        return $data;
    }

    public function requestServerToken(): array
    {
        $config = config('services.iracing');

        $this->ensureConfigValues([
            'client_id' => $config['client_id'] ?? null,
            'client_secret' => $config['client_secret'] ?? null,
            'server_username' => $config['server_username'] ?? null,
            'server_password' => $config['server_password'] ?? null,
        ]);

        $response = Http::asForm()->post(
            rtrim($config['oauth_base_url'], '/').'/oauth2/token',
            [
                'grant_type' => 'password_limited',
                'username' => $config['server_username'],
                'password' => $this->maskSecret($config['server_password'], $config['server_username']),
                'client_id' => $config['client_id'],
                'client_secret' => $this->maskSecret($config['client_secret'], $config['client_id']),
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
        $url = rtrim($config['api_base_url'], '/').'/'.ltrim($endpoint, '/');

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
            throw new RuntimeException('iRacing request failed with status '.$response->status());
        }

        $json = $response->json();

        if (! is_array($json)) {
            throw new RuntimeException('iRacing response is not valid JSON.');
        }

        return $json;
    }

    private function resolveSignedUrlPayload(array $payload, string $accessToken): array
    {
        $signedUrl = Arr::get($payload, 'link');

        if (! is_string($signedUrl) || $signedUrl === '') {
            return $payload;
        }

        $response = Http::withToken($accessToken)->get($signedUrl);

        return $this->decodeResponse($response);
    }

    private function assertTokenPayload(array $payload): void
    {
        if (empty($payload['access_token'])) {
            throw new RuntimeException('iRacing token response does not include access_token.');
        }
    }
}
