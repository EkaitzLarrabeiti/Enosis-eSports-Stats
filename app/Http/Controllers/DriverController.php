<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\IRacingApiService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;

class DriverController extends Controller
{
    public function __construct(private readonly IRacingApiService $iracingApiService)
    {
    }

    public function profile(): View
    {
        $user = auth()->user();

        if (! $user) {
            return view('driver.profile', [
                'user' => null,
                'stats' => null,
                'results' => collect(),
            ]);
        }

        $stats = null;
        $results = collect();
        $iracingNotice = null;

        // Comprobaciones de vinculación: avisos si faltan tokens o están caducados.
        if ($user->iracing_linked && empty($user->access_token)) {
            $iracingNotice = 'Tu cuenta de iRacing esta vinculada, pero no hay token de acceso guardado. Vuelve a vincular la cuenta.';
        }

        if (
            $user->iracing_linked
            && $user->token_expires_at
            && now()->gte($user->token_expires_at)
            && empty($user->refresh_token)
        ) {
            $iracingNotice = 'El token de iRacing ha caducado y no hay refresh_token. Vuelve a vincular la cuenta.';
        }

        if ($user->iracing_linked && ! empty($user->access_token)) {
            try {
                $currentYear = now()->year;
                // Fuentes del dashboard:
                // - member/info: perfil + cust_id
                // - member_summary: snapshot de SR/iRating
                // - member_recent_races: últimas carreras (tarjetas + tabla)
                $memberInfo = $this->iracingApiService->getForUser($user, 'data/member/info');
                $summary = $this->iracingApiService->getForUser($user, 'data/stats/member_summary');
                $recentRaces = $this->iracingApiService->getForUser($user, 'data/stats/member_recent_races');
                $yearly = $this->iracingApiService->getForUser($user, 'data/stats/member_yearly');
                $yearRecap = $this->iracingApiService->getForUser($user, 'data/stats/member_recap', [
                    'year' => $currentYear,
                ]);
                $career = $this->iracingApiService->getForUser($user, 'data/stats/member_career');

                // Normalizamos carreras para la UI (license_key, deltas de iRating, etc.).
                $recentRaceList = collect(data_get($recentRaces, 'races', []));
                $seriesLogoMap = $this->buildSeriesLogoMap($user, $recentRaceList);
                $allResults = $this->mapRecentRaces($recentRaceList, $seriesLogoMap);
                $results = $allResults->take(10)->values();
                // Construimos estadísticas agregadas con últimos resultados + summary.
                $stats = $this->buildLiveStats($summary, $results, $memberInfo);
                // El gráfico usa chart_data por categoría de licencia.
                $chartSeriesFromApi = $this->buildChartSeriesFromApi($user);
                // Estadísticas por licencia desde inicio (member_career).
                $careerLicenseStats = $this->buildCareerLicenseStats($career);
                $yearStats = $this->buildYearlyStats($yearly, $currentYear);
                $yearFavorites = $this->buildYearRecapFavorites($yearRecap);
            } catch (RuntimeException $exception) {
                report($exception);
                $iracingNotice = 'No se pudieron cargar los datos de iRacing. Intenta vincular la cuenta de nuevo.';
                if (config('app.debug')) {
                    $iracingNotice .= ' Detalle: '.$exception->getMessage();
                }
            }
        }

        return view('driver.profile', [
            'user' => $user,
            'stats' => $stats,
            'results' => $results,
            'chartResults' => $allResults ?? $results,
            'chartSeriesFromApi' => $chartSeriesFromApi ?? null,
            'careerLicenseStats' => $careerLicenseStats ?? [],
            'yearStats' => $yearStats ?? [],
            'yearFavorites' => $yearFavorites ?? [],
            'currentYear' => $currentYear ?? now()->year,
            'iracingNotice' => $iracingNotice,
        ]);
    }

    private function buildLiveStats(array $summary, Collection $results, ?array $licensePayload): array
    {
        // Construimos stats principales para las tarjetas del header.
        $wins = $results->where('finish_position', 1)->count();
        $podiums = $results->whereIn('finish_position', [1, 2, 3])->count();
        $poles = $results->where('starting_position', 1)->count();
        $latestRating = (int) ($results->first()?->newi_rating ?? 0);

        $safetyRating = (string) (
            data_get($summary, 'safety_rating')
            ?? data_get($summary, 'data.safety_rating')
            ?? data_get($summary, 'member_summary.safety_rating')
            ?? '-'
        );

        return [
            'wins' => $wins,
            'podiums' => $podiums,
            'poles' => $poles,
            'irating' => $latestRating,
            'safety_rating' => $safetyRating,
            'last_synced_at' => now(),
            'licenses' => $this->extractLicenseRatings($licensePayload ?? $summary),
        ];
    }

    private function mapRecentRaces(Collection $races, array $seriesLogos = []): Collection
    {
        return $races->map(function (array $race) use ($seriesLogos) {
            // Normalizamos el payload de carreras a una forma estable para la UI.
            $startTime = data_get($race, 'session_start_time');
            $raceDate = $startTime ? Carbon::parse($startTime) : null;
            $oldRating = (int) data_get($race, 'oldi_rating', 0);
            $newRating = (int) data_get($race, 'newi_rating', 0);
            $seriesId = $this->resolveSeriesIdFromRace($race);
            $trackName = data_get($race, 'track.track_name') ?? data_get($race, 'track_name');
            $categoryId = data_get($race, 'license_category_id')
                ?? data_get($race, 'category_id')
                ?? data_get($race, 'license_category');
            $groupId = data_get($race, 'license_group_id') ?? data_get($race, 'group_id');
            $groupName = data_get($race, 'license_group_name')
                ?? data_get($race, 'license_group')
                ?? data_get($race, 'group_name')
                ?? data_get($race, 'category');
            $strengthOfField = data_get($race, 'strength_of_field')
                ?? data_get($race, 'sof')
                ?? data_get($race, 'strength_of_field_rating')
                ?? data_get($race, 'field_strength')
                ?? data_get($race, 'field_strength_rating');
            $seriesLogo = data_get($race, 'series_logo')
                ?? data_get($race, 'series_logo_url')
                ?? data_get($race, 'series.logo')
                ?? data_get($race, 'series.logo_url')
                ?? data_get($race, 'series.image')
                ?? data_get($race, 'series_image')
                ?? data_get($race, 'series_image_url')
                ?? data_get($race, 'series_logo_small')
                ?? data_get($race, 'series_logo_medium')
                ?? data_get($race, 'series_logo_large')
                ?? data_get($race, 'series.logo_small')
                ?? data_get($race, 'series.logo_medium')
                ?? data_get($race, 'series.logo_large')
                ?? data_get($race, 'series.logo_url_small')
                ?? data_get($race, 'series.logo_url_medium')
                ?? data_get($race, 'series.logo_url_large')
                ?? data_get($race, 'series_logo_small_url')
                ?? data_get($race, 'series_logo_medium_url')
                ?? data_get($race, 'series_logo_large_url');
            $trackLogo = data_get($race, 'track.track_logo')
                ?? data_get($race, 'track.logo')
                ?? data_get($race, 'track.logo_url')
                ?? data_get($race, 'track.image')
                ?? data_get($race, 'track_image')
                ?? data_get($race, 'track_logo')
                ?? data_get($race, 'track_logo_url');
            if (empty($seriesLogo) && $seriesId && isset($seriesLogos[$seriesId])) {
                $seriesLogo = $seriesLogos[$seriesId];
            }

            if (empty($trackLogo) && is_string($trackName) && trim($trackName) !== '') {
                $trackLogo = $this->buildTrackLogoUrl($trackName);
            }

            if (config('app.debug') && empty($trackLogo)) {
                logger()->info('iRacing race track logo missing', [
                    'track_name' => $trackName,
                    'track' => data_get($race, 'track'),
                    'track_logo' => data_get($race, 'track_logo'),
                    'track_logo_url' => data_get($race, 'track_logo_url'),
                    'track_image' => data_get($race, 'track_image'),
                ]);
            }

            $licenseKey = $this->resolveRaceLicenseKey($groupName, $categoryId, $groupId);

            return (object) [
                'series_name' => data_get($race, 'series_name'),
                'series_logo' => $seriesLogo,
                'series_id' => $seriesId,
                'race_date' => $raceDate,
                'track_name' => $trackName,
                'track_logo' => $trackLogo,
                'finish_position' => data_get($race, 'finish_position'),
                'starting_position' => data_get($race, 'start_position'),
                'incidents' => data_get($race, 'incidents'),
                'subsession_id' => data_get($race, 'subsession_id'),
                'strength_of_field' => $strengthOfField,
                'license_category_id' => $categoryId,
                'license_group_id' => $groupId,
                'license_group_name' => $groupName,
                'license_key' => $licenseKey,
                'oldi_rating' => $oldRating,
                'newi_rating' => $newRating,
                'irating_change' => $newRating - $oldRating,
            ];
        });
    }

    private function extractLicenseRatings(array $payload): array
    {
        $ratings = [
            'sports_car' => ['ir' => 0, 'sr' => '-', 'class' => 'R', 'class_border' => 'border-red-500', 'class_text' => 'text-red-300', 'class_color' => 'border-red-500 text-red-300'],
            'formula_car' => ['ir' => 0, 'sr' => '-', 'class' => 'R', 'class_border' => 'border-red-500', 'class_text' => 'text-red-300', 'class_color' => 'border-red-500 text-red-300'],
            'oval' => ['ir' => 0, 'sr' => '-', 'class' => 'R', 'class_border' => 'border-red-500', 'class_text' => 'text-red-300', 'class_color' => 'border-red-500 text-red-300'],
            'dirt_road' => ['ir' => 0, 'sr' => '-', 'class' => 'R', 'class_border' => 'border-red-500', 'class_text' => 'text-red-300', 'class_color' => 'border-red-500 text-red-300'],
            'dirt_oval' => ['ir' => 0, 'sr' => '-', 'class' => 'R', 'class_border' => 'border-red-500', 'class_text' => 'text-red-300', 'class_color' => 'border-red-500 text-red-300'],
            'road' => ['ir' => 0, 'sr' => '-', 'class' => 'R', 'class_border' => 'border-red-500', 'class_text' => 'text-red-300', 'class_color' => 'border-red-500 text-red-300'],
        ];

        $licenses = $this->resolveLicensesArray($payload);

        foreach ($licenses as $licenseKey => $license) {
            if (is_array($license) && isset($license['license']) && is_array($license['license'])) {
                $license = $license['license'];
            }

            if (! is_array($license)) {
                continue;
            }

            $key = null;
            if (is_string($licenseKey)) {
                $key = $this->normalizeLicenseKey($licenseKey);
            }

            $key = $key ?? $this->resolveLicenseKey($license);
            if (! $key || ! array_key_exists($key, $ratings)) {
                continue;
            }

            $irating = data_get($license, 'irating')
                ?? data_get($license, 'i_rating')
                ?? data_get($license, 'irating_value')
                ?? data_get($license, 'rating')
                ?? data_get($license, 'rating.irating')
                ?? data_get($license, 'rating.value')
                ?? data_get($license, 'ratings.irating');

            $safetyRating = data_get($license, 'safety_rating')
                ?? data_get($license, 'sr')
                ?? data_get($license, 'license_sr')
                ?? data_get($license, 'rating.safety_rating')
                ?? data_get($license, 'rating.sr');

            if ($irating !== null) {
                $ratings[$key]['ir'] = (int) $irating;
            }

            if ($safetyRating !== null) {
                $ratings[$key]['sr'] = $this->formatSafetyRating($safetyRating);
            }

            $classInfo = $this->resolveLicenseClassInfo($license, $safetyRating ?? null);
            $ratings[$key]['class'] = $classInfo['label'];
            $ratings[$key]['class_border'] = $classInfo['border'];
            $ratings[$key]['class_text'] = $classInfo['text'];
            $ratings[$key]['class_color'] = $classInfo['border'].' '.$classInfo['text'];
            $ratings[$key]['class_color_hex'] = $classInfo['color_hex'];
        }

        if (
            $this->isEmptyRating($ratings['sports_car'])
            && $this->isEmptyRating($ratings['formula_car'])
            && ! $this->isEmptyRating($ratings['road'])
        ) {
            $ratings['sports_car'] = $ratings['road'];
        }

        return $ratings;
    }

    private function resolveSeriesIdFromRace(array $race): ?int
    {
        $seriesId = data_get($race, 'series_id')
            ?? data_get($race, 'seriesid')
            ?? data_get($race, 'series.series_id')
            ?? data_get($race, 'series.id');

        if ($seriesId === null || $seriesId === '') {
            return null;
        }

        return is_numeric($seriesId) ? (int) $seriesId : null;
    }

    private function isEmptyRating(array $rating): bool
    {
        return ((int) ($rating['ir'] ?? 0)) <= 0 && (string) ($rating['sr'] ?? '-') === '-';
    }

    private function normalizeLicenseKey(string $value): ?string
    {
        $normalized = strtolower(trim($value));
        $normalized = str_replace([' ', '-', '/'], '_', $normalized);

        if (str_contains($normalized, 'sports')) {
            return 'sports_car';
        }
        if (str_contains($normalized, 'formula')) {
            return 'formula_car';
        }
        if (str_contains($normalized, 'dirt') && str_contains($normalized, 'oval')) {
            return 'dirt_oval';
        }
        if (str_contains($normalized, 'dirt') && str_contains($normalized, 'road')) {
            return 'dirt_road';
        }
        if (str_contains($normalized, 'oval')) {
            return 'oval';
        }
        if (str_contains($normalized, 'road')) {
            return 'road';
        }

        return null;
    }

    private function resolveLicenseClassInfo(array $license, $safetyRating): array
    {
        $groupLevel = data_get($license, 'group_level')
            ?? data_get($license, 'license_group_level')
            ?? data_get($license, 'group_level_name')
            ?? data_get($license, 'group_name')
            ?? data_get($license, 'license_group_name')
            ?? data_get($license, 'license.group_level')
            ?? data_get($license, 'rating.group_level');

        $class = data_get($license, 'license_class')
            ?? data_get($license, 'class')
            ?? data_get($license, 'license_class_id')
            ?? data_get($license, 'license_class_name')
            ?? data_get($license, 'class_name')
            ?? data_get($license, 'class_letter')
            ?? data_get($license, 'license_class_letter')
            ?? data_get($license, 'category_class')
            ?? data_get($license, 'level')
            ?? data_get($license, 'lic_class')
            ?? data_get($license, 'class_id')
            ?? data_get($license, 'rating.license_class')
            ?? data_get($license, 'rating.license_class_id')
            ?? data_get($license, 'rating.license_class_name');

        if (is_string($safetyRating) && preg_match('/^[A-Za-z]/', $safetyRating)) {
            $class = strtoupper(substr(trim($safetyRating), 0, 1));
        }

        $label = 'R';
        $labelFromGroup = false;
        if (is_string($groupLevel) && $groupLevel !== '') {
            $normalizedGroup = strtoupper(trim($groupLevel));
            if (str_contains($normalizedGroup, 'ROOKIE')) {
                $label = 'R';
            } elseif (preg_match('/([A-Z])\\s*$/', $normalizedGroup, $matches)) {
                $label = $matches[1];
            } elseif (preg_match('/([A-Z])/', $normalizedGroup, $matches)) {
                $label = $matches[1];
            } else {
                $label = substr($normalizedGroup, -1);
            }
            $labelFromGroup = true;
        }

        if (! $labelFromGroup && is_numeric($class)) {
            $numericClass = (int) $class;
            $label = match (true) {
                $numericClass >= 0 && $numericClass <= 5 => match ($numericClass) {
                    0 => 'R',
                    1 => 'D',
                    2 => 'C',
                    3 => 'B',
                    4 => 'A',
                    5 => 'P',
                    default => 'R',
                },
                $numericClass >= 1 && $numericClass <= 6 => match ($numericClass) {
                    1 => 'R',
                    2 => 'D',
                    3 => 'C',
                    4 => 'B',
                    5 => 'A',
                    6 => 'P',
                    default => 'R',
                },
                default => 'R',
            };
        } elseif (! $labelFromGroup && is_string($class) && $class !== '') {
            $normalized = strtoupper(trim($class));
            if (str_contains($normalized, 'ROOKIE')) {
                $label = 'R';
            } elseif (str_contains($normalized, 'PRO')) {
                $label = 'P';
            } else {
                $label = substr($normalized, 0, 1);
            }
        }

        $colorHex = $this->normalizeHexColor(
            data_get($license, 'color')
                ?? data_get($license, 'license_color')
                ?? data_get($license, 'rating.color')
        );

        $border = match ($label) {
            'A' => 'border-blue-500',
            'B' => 'border-emerald-500',
            'C' => 'border-yellow-500',
            'D' => 'border-orange-500',
            'P' => 'border-purple-500',
            default => 'border-red-500',
        };

        $text = match ($label) {
            'A' => 'text-blue-300',
            'B' => 'text-emerald-300',
            'C' => 'text-yellow-300',
            'D' => 'text-orange-300',
            'P' => 'text-purple-300',
            default => 'text-red-300',
        };

        return ['label' => $label, 'border' => $border, 'text' => $text, 'color_hex' => $colorHex];
    }

    private function normalizeHexColor($value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $hex = ltrim(trim($value), '#');
        if ($hex === '') {
            return null;
        }

        if (! preg_match('/^[0-9a-fA-F]{6}([0-9a-fA-F]{2})?$/', $hex)) {
            return null;
        }

        return '#'.strtolower($hex);
    }

    private function formatSafetyRating($value): string
    {
        if (is_numeric($value)) {
            return number_format((float) $value, 2);
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === '') {
                return '-';
            }

            if (preg_match('/([0-9]+(?:\\.[0-9]+)?)/', $trimmed, $matches)) {
                return number_format((float) $matches[1], 2);
            }

            return $trimmed;
        }

        return '-';
    }

    private function resolveLicensesArray(array $payload): array
    {
        $members = data_get($payload, 'members');
        if (is_array($members) && $members !== []) {
            $member = $members[0] ?? null;
            if (is_array($member)) {
                $licenses = data_get($member, 'licenses') ?? data_get($member, 'license_data');
                if (is_array($licenses)) {
                    return $licenses;
                }
            }
        }

        $licenses = data_get($payload, 'licenses')
            ?? data_get($payload, 'license_data')
            ?? data_get($payload, 'member_info.licenses')
            ?? data_get($payload, 'member_info.license_data');
        if (is_array($licenses)) {
            return $licenses;
        }

        return [];
    }

    private function resolveLicenseKey(array $license): ?string
    {
        $categoryId = data_get($license, 'category_id');
        $rawName = data_get($license, 'group_name')
            ?? data_get($license, 'license_group')
            ?? data_get($license, 'license_group_name')
            ?? data_get($license, 'category')
            ?? data_get($license, 'category_name');

        $normalized = strtolower((string) $rawName);
        $normalized = str_replace([' ', '-', '/'], '_', $normalized);

        if (str_contains($normalized, 'sports')) {
            return 'sports_car';
        }
        if (str_contains($normalized, 'formula')) {
            return 'formula_car';
        }
        if (str_contains($normalized, 'road')) {
            return 'road';
        }
        if (str_contains($normalized, 'dirt') && str_contains($normalized, 'oval')) {
            return 'dirt_oval';
        }
        if (str_contains($normalized, 'dirt') && str_contains($normalized, 'road')) {
            return 'dirt_road';
        }
        if (str_contains($normalized, 'oval')) {
            return 'oval';
        }

        return match ((int) $categoryId) {
            1 => 'oval',
            3 => 'dirt_oval',
            4 => 'dirt_road',
            5 => 'sports_car',
            6 => 'formula_car',
            2 => 'road',
            default => null,
        };
    }

    private function resolveRaceLicenseKey(?string $groupName, $categoryId, $groupId): string
    {
        $normalized = strtolower((string) $groupName);
        $normalized = str_replace([' ', '-', '/'], '_', $normalized);

        if ($normalized !== '') {
            if (str_contains($normalized, 'sports')) {
                return 'sports_car';
            }
            if (str_contains($normalized, 'formula')) {
                return 'formula_car';
            }
            if (str_contains($normalized, 'dirt') && str_contains($normalized, 'oval')) {
                return 'dirt_oval';
            }
            if (str_contains($normalized, 'dirt') && str_contains($normalized, 'road')) {
                return 'dirt_road';
            }
            if (str_contains($normalized, 'oval')) {
                return 'oval';
            }
            if (str_contains($normalized, 'road')) {
                return 'road';
            }
        }

        if (is_string($categoryId) && $categoryId !== '' && ! is_numeric($categoryId)) {
            $normalizedCategory = $this->normalizeLicenseKey($categoryId);
            if ($normalizedCategory) {
                return $normalizedCategory;
            }
        }

        return match ((int) $categoryId) {
            1 => 'oval',
            3 => 'dirt_oval',
            4 => 'dirt_road',
            5 => 'sports_car',
            6 => 'formula_car',
            2 => 'road',
            default => 'road',
        };
    }

    private function buildChartSeriesFromApi(User $user): array
    {
        $series = [];
        $categories = [
            'road' => 2,
            'oval' => 1,
            'dirt_oval' => 3,
            'dirt_road' => 4,
            'sports_car' => 5,
            'formula_car' => 6,
        ];

        foreach ($categories as $key => $categoryId) {
            if (! is_numeric($categoryId)) {
                continue;
            }

            $query = [
                'category_id' => $categoryId,
                'chart_type' => 1,
            ];

            if (! empty($user->iracing_customer_id)) {
                $query['cust_id'] = $user->iracing_customer_id;
            }

            try {
                // chart_data devuelve histórico de iRating por categoría.
                $cacheKey = 'iracing.chart_data.'.(string) $user->id.'.'.(string) $categoryId.'.1';
                $payload = cache()->remember($cacheKey, 600, function () use ($user, $query) {
                    return $this->iracingApiService->getForUser($user, 'data/member/chart_data', $query);
                });
            } catch (RuntimeException $exception) {
                report($exception);
                continue;
            }

            $grouped = $this->extractSeriesGroups($payload);
            if ($grouped !== []) {
                foreach ($grouped as $groupKey => $groupSeries) {
                    if (! isset($series[$groupKey]) || $series[$groupKey]['count'] < $groupSeries['count']) {
                        $series[$groupKey] = $groupSeries;
                    }
                }
                continue;
            }

            $normalized = $this->normalizeChartSeries($payload);
            if ($normalized['count'] > 0) {
                $series[$key] = $normalized;
            }
        }

        if (! empty($series)) {
            $series['sports_car'] = $series['sports_car'] ?? ['series' => [], 'count' => 0];
            $series['formula_car'] = $series['formula_car'] ?? ['series' => [], 'count' => 0];
        }

        return $series;
    }

    private function buildSeriesLogoMap(User $user, Collection $races): array
    {
        $seriesIds = $races
            ->map(fn (array $race) => $this->resolveSeriesIdFromRace($race))
            ->filter(fn ($id) => ! empty($id))
            ->unique()
            ->values();

        if ($seriesIds->isEmpty()) {
            return [];
        }

        $assetMap = $this->extractSeriesLogoMapFromAssets($this->fetchSeriesAssets($user));
        $logos = [];
        foreach ($seriesIds as $seriesId) {
            $seriesId = (int) $seriesId;
            if (isset($assetMap[$seriesId])) {
                $logos[$seriesId] = $assetMap[$seriesId];
                continue;
            }
            $logos[$seriesId] = $this->buildSeriesLogoUrl($seriesId);
        }

        return $logos;
    }

    private function fetchSeriesAssets(User $user): array
    {
        return cache()->remember('iracing.series.assets', 86400, function () use ($user) {
            try {
                $payload = $this->iracingApiService->getForUser($user, 'data/series/assets');
                return is_array($payload) ? $payload : [];
            } catch (RuntimeException $exception) {
                report($exception);
                return [];
            }
        });
    }

    private function extractSeriesLogoMapFromAssets(array $payload): array
    {
        $candidates = [
            data_get($payload, 'series'),
            data_get($payload, 'data.series'),
            data_get($payload, 'data'),
            data_get($payload, 'results'),
            $payload,
        ];

        $items = [];
        foreach ($candidates as $candidate) {
            if (is_object($candidate)) {
                $candidate = (array) $candidate;
            }
            if (! is_array($candidate) || $candidate === []) {
                continue;
            }
            if ($this->isListArray($candidate)) {
                $items = $candidate;
                break;
            }
        }

        if ($items === []) {
            return [];
        }

        $map = [];
        $logoKeys = [
            'series_logo',
            'series_logo_url',
            'logo',
            'logo_url',
            'logo_small',
            'logo_medium',
            'logo_large',
            'logo_url_small',
            'logo_url_medium',
            'logo_url_large',
            'image',
            'image_url',
        ];

        foreach ($items as $item) {
            if (is_object($item)) {
                $item = (array) $item;
            }
            if (! is_array($item)) {
                continue;
            }
            $seriesId = data_get($item, 'series_id') ?? data_get($item, 'id');
            if (! is_numeric($seriesId)) {
                continue;
            }
            $logo = $this->extractImageValue($item, $logoKeys);
            if (! $logo) {
                continue;
            }
            $map[(int) $seriesId] = $this->normalizeImageUrl($logo);
        }

        return $map;
    }

    private function buildSeriesLogoUrl(int $seriesId): string
    {
        return 'https://images-static.iracing.com/img/logos/series/seriesid_'.$seriesId.'.png';
    }

    private function buildTrackLogoUrl(string $trackName): string
    {
        $slug = $this->normalizeTrackSlug($trackName);
        return $slug !== ''
            ? 'https://images-static.iracing.com/img/logos/tracks/'.$slug.'-logo.png'
            : '';
    }

    private function normalizeTrackSlug(string $trackName): string
    {
        $ascii = Str::ascii($trackName);
        $normalized = strtolower($ascii);
        $normalized = preg_replace('/[^a-z0-9]/', '', $normalized);
        return $normalized ?? '';
    }

    private function fetchSeriesLogo(User $user, int $seriesId): ?string
    {
        if ($seriesId <= 0) {
            return null;
        }

        $cacheKey = 'iracing.series.logo.'.(string) $seriesId;

        return cache()->remember($cacheKey, 86400, function () use ($user, $seriesId) {
            $payload = $this->fetchSeriesPayload($user, $seriesId);
            if ($payload === []) {
                return null;
            }

            $logo = $this->extractSeriesLogoFromPayload($payload);
            return $logo ? $this->normalizeImageUrl($logo) : null;
        });
    }

    private function fetchSeriesPayload(User $user, int $seriesId): array
    {
        $endpoints = [
            ['data/series/get', ['series_id' => $seriesId]],
            ['data/series/series', ['series_id' => $seriesId]],
        ];

        foreach ($endpoints as [$endpoint, $query]) {
            try {
                $payload = $this->iracingApiService->getForUser($user, $endpoint, $query);
                if (is_array($payload) && $payload !== []) {
                    return $payload;
                }
            } catch (RuntimeException $exception) {
                report($exception);
                continue;
            }
        }

        return [];
    }

    private function extractSeriesLogoFromPayload(array $payload): ?string
    {
        $keys = [
            'series_logo',
            'series_logo_url',
            'logo',
            'logo_url',
            'logo_small',
            'logo_medium',
            'logo_large',
            'logo_url_small',
            'logo_url_medium',
            'logo_url_large',
            'image',
            'image_url',
        ];

        $candidates = [
            data_get($payload, 'series'),
            data_get($payload, 'data.series'),
            data_get($payload, 'data'),
            data_get($payload, 'series_data'),
            data_get($payload, 'results'),
            $payload,
        ];

        foreach ($candidates as $candidate) {
            if (is_object($candidate)) {
                $candidate = (array) $candidate;
            }

            if (! is_array($candidate) || $candidate === []) {
                continue;
            }

            if ($this->isListArray($candidate)) {
                foreach ($candidate as $item) {
                    if (is_object($item)) {
                        $item = (array) $item;
                    }
                    if (! is_array($item)) {
                        continue;
                    }
                    $logo = $this->extractImageValue($item, $keys);
                    if ($logo) {
                        return $logo;
                    }
                }
                continue;
            }

            $logo = $this->extractImageValue($candidate, $keys);
            if ($logo) {
                return $logo;
            }
        }

        return null;
    }

    private function normalizeImageUrl(string $value): string
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return $trimmed;
        }

        if (str_starts_with($trimmed, 'http://') || str_starts_with($trimmed, 'https://')) {
            return $trimmed;
        }

        $path = ltrim($trimmed, '/');
        return 'https://images-static.iracing.com/'.$path;
    }

    private function buildCareerLicenseStats(array $payload): array
    {
        $statsList = data_get($payload, 'stats')
            ?? data_get($payload, 'data.stats')
            ?? data_get($payload, 'data')
            ?? $payload;

        if (! is_array($statsList)) {
            return [];
        }

        $results = [];

        foreach ($statsList as $item) {
            if (is_object($item)) {
                $item = (array) $item;
            }

            if (! is_array($item)) {
                continue;
            }

            $categoryName = data_get($item, 'category')
                ?? data_get($item, 'category_name')
                ?? data_get($item, 'license_category')
                ?? data_get($item, 'license_category_name');
            $categoryId = data_get($item, 'category_id') ?? data_get($item, 'license_category_id');
            $licenseKey = $this->resolveLicenseKey([
                'category_id' => $categoryId,
                'category' => $categoryName,
                'category_name' => $categoryName,
                'group_name' => $categoryName,
            ]);

            if (! $licenseKey) {
                continue;
            }

            $total = data_get($item, 'starts')
                ?? data_get($item, 'start_count')
                ?? data_get($item, 'races')
                ?? data_get($item, 'total_starts')
                ?? 0;

            if ((int) $total <= 0) {
                continue;
            }

            $results[$licenseKey] = [
                'total' => (int) $total,
                'wins' => (int) (data_get($item, 'wins') ?? 0),
                'top5' => (int) (data_get($item, 'top5') ?? 0),
                'poles' => (int) (data_get($item, 'poles') ?? 0),
                'laps_led' => (int) (
                    data_get($item, 'laps_led')
                    ?? data_get($item, 'laps_lead')
                    ?? data_get($item, 'laps_led_count')
                    ?? data_get($item, 'lead_laps')
                    ?? data_get($item, 'led_laps')
                    ?? data_get($item, 'laps_leader')
                    ?? 0
                ),
                'avg_start' => data_get($item, 'avg_start_position')
                    ?? data_get($item, 'avg_start')
                    ?? null,
                'avg_finish' => data_get($item, 'avg_finish_position')
                    ?? data_get($item, 'avg_finish')
                    ?? null,
                'avg_inc' => data_get($item, 'avg_incidents')
                    ?? data_get($item, 'avg_inc')
                    ?? null,
            ];
        }

        return $results;
    }

    private function buildYearlyStats(array $payload, int $year): array
    {
        $statsList = data_get($payload, 'stats')
            ?? data_get($payload, 'data.stats')
            ?? data_get($payload, 'data')
            ?? $payload;

        if (! is_array($statsList)) {
            return [];
        }

        if (! $this->isListArray($statsList)) {
            $normalized = [];
            foreach ($statsList as $key => $value) {
                if (is_numeric($key) && is_array($value)) {
                    $normalized[] = [
                        'year' => (int) $key,
                        'stats' => $value,
                    ];
                }
            }
            if ($normalized !== []) {
                $statsList = $normalized;
            }
        }

        $totals = [
            'starts' => 0,
            'wins' => 0,
            'top5' => 0,
            'poles' => 0,
            'laps' => 0,
            'laps_led' => 0,
        ];

        $avgBuckets = [
            'avg_start' => ['sum' => 0.0, 'weight' => 0],
            'avg_finish' => ['sum' => 0.0, 'weight' => 0],
            'avg_inc' => ['sum' => 0.0, 'weight' => 0],
        ];

        $processItem = function (array $item, int $targetYear) use (&$totals, &$avgBuckets): void {
            $itemYear = (int) (data_get($item, 'year') ?? data_get($item, 'season_year') ?? 0);
            if ($itemYear !== 0 && $itemYear !== $targetYear) {
                return;
            }

            $starts = (int) (
                data_get($item, 'starts')
                ?? data_get($item, 'start_count')
                ?? data_get($item, 'races')
                ?? data_get($item, 'total_starts')
                ?? 0
            );

            if ($starts <= 0) {
                return;
            }

            $totals['starts'] += $starts;
            $totals['wins'] += (int) (data_get($item, 'wins') ?? 0);
            $totals['top5'] += (int) (data_get($item, 'top5') ?? 0);
            $totals['poles'] += (int) (data_get($item, 'poles') ?? 0);
            $totals['laps'] += (int) (data_get($item, 'laps') ?? 0);
            $totals['laps_led'] += (int) (
                data_get($item, 'laps_led')
                ?? data_get($item, 'laps_lead')
                ?? data_get($item, 'laps_led_count')
                ?? data_get($item, 'lead_laps')
                ?? data_get($item, 'led_laps')
                ?? data_get($item, 'laps_leader')
                ?? 0
            );

            $avgStart = data_get($item, 'avg_start_position')
                ?? data_get($item, 'avg_start');
            if ($avgStart !== null && $avgStart !== '') {
                $avgBuckets['avg_start']['sum'] += (float) $avgStart * $starts;
                $avgBuckets['avg_start']['weight'] += $starts;
            }

            $avgFinish = data_get($item, 'avg_finish_position')
                ?? data_get($item, 'avg_finish');
            if ($avgFinish !== null && $avgFinish !== '') {
                $avgBuckets['avg_finish']['sum'] += (float) $avgFinish * $starts;
                $avgBuckets['avg_finish']['weight'] += $starts;
            }

            $avgInc = data_get($item, 'avg_incidents')
                ?? data_get($item, 'avg_inc');
            if ($avgInc !== null && $avgInc !== '') {
                $avgBuckets['avg_inc']['sum'] += (float) $avgInc * $starts;
                $avgBuckets['avg_inc']['weight'] += $starts;
            }
        };

        foreach ($statsList as $item) {
            if (is_object($item)) {
                $item = (array) $item;
            }

            if (! is_array($item)) {
                continue;
            }

            if (isset($item['stats']) && is_array($item['stats']) && ! isset($item['starts'])) {
                $nestedYear = (int) (data_get($item, 'year') ?? data_get($item, 'season_year') ?? $year);
                foreach ($item['stats'] as $nested) {
                    if (is_object($nested)) {
                        $nested = (array) $nested;
                    }
                    if (! is_array($nested)) {
                        continue;
                    }
                    if (! isset($nested['year'])) {
                        $nested['year'] = $nestedYear;
                    }
                    $processItem($nested, $year);
                }
                continue;
            }

            $processItem($item, $year);
        }

        $avgStart = $avgBuckets['avg_start']['weight'] > 0
            ? $avgBuckets['avg_start']['sum'] / $avgBuckets['avg_start']['weight']
            : null;
        $avgFinish = $avgBuckets['avg_finish']['weight'] > 0
            ? $avgBuckets['avg_finish']['sum'] / $avgBuckets['avg_finish']['weight']
            : null;
        $avgInc = $avgBuckets['avg_inc']['weight'] > 0
            ? $avgBuckets['avg_inc']['sum'] / $avgBuckets['avg_inc']['weight']
            : null;

        $starts = $totals['starts'];
        $winPct = $starts > 0 ? round(($totals['wins'] / $starts) * 100, 1) : 0;
        $top5Pct = $starts > 0 ? round(($totals['top5'] / $starts) * 100, 1) : 0;

        return array_merge($totals, [
            'avg_start' => $avgStart,
            'avg_finish' => $avgFinish,
            'avg_inc' => $avgInc,
            'win_pct' => $winPct,
            'top5_pct' => $top5Pct,
        ]);
    }

    private function buildYearRecapFavorites(array $payload): array
    {
        $stats = data_get($payload, 'stats')
            ?? data_get($payload, 'data.stats')
            ?? $payload;

        $track = data_get($stats, 'favorite_track')
            ?? data_get($payload, 'favorite_track')
            ?? data_get($payload, 'favorite_track_name')
            ?? data_get($payload, 'favorite.track')
            ?? data_get($payload, 'favorite.track_name')
            ?? data_get($payload, 'track')
            ?? data_get($payload, 'track_name')
            ?? data_get($payload, 'data.favorite_track')
            ?? data_get($payload, 'data.favorite_track_name')
            ?? data_get($payload, 'data.favorite.track')
            ?? data_get($payload, 'data.favorite.track_name')
            ?? data_get($payload, 'data.track')
            ?? data_get($payload, 'data.track_name');
        $car = data_get($stats, 'favorite_car')
            ?? data_get($payload, 'favorite_car')
            ?? data_get($payload, 'favorite.car');
        $series = data_get($payload, 'favorite_series')
            ?? data_get($payload, 'favorite_series_name')
            ?? data_get($payload, 'favorite.series')
            ?? data_get($payload, 'favorite.series_name')
            ?? data_get($payload, 'series')
            ?? data_get($payload, 'series_name')
            ?? data_get($payload, 'data.favorite_series')
            ?? data_get($payload, 'data.favorite_series_name')
            ?? data_get($payload, 'data.favorite.series')
            ?? data_get($payload, 'data.favorite.series_name')
            ?? data_get($payload, 'data.series')
            ?? data_get($payload, 'data.series_name');

        if ($series === null && $car !== null) {
            $series = $car;
        }

        return [
            'track' => $this->extractNameValue($track, ['track_name', 'name']),
            'car' => $this->extractNameValue($series, ['car_name', 'series_name', 'name']),
            'track_image' => $this->extractImageValue($track, ['track_logo', 'logo', 'image', 'image_url']),
            'car_image' => $this->extractImageValue($series, ['car_image', 'image', 'image_url', 'logo']),
        ];
    }

    private function extractNameValue($value, array $keys): ?string
    {
        if (is_string($value)) {
            $trimmed = trim($value);
            return $trimmed !== '' ? $trimmed : null;
        }

        if (is_object($value)) {
            $value = (array) $value;
        }

        if (is_array($value)) {
            foreach ($keys as $key) {
                if (isset($value[$key]) && is_string($value[$key]) && trim($value[$key]) !== '') {
                    return trim($value[$key]);
                }
            }
        }

        return null;
    }

    private function extractImageValue($value, array $keys): ?string
    {
        if (is_string($value)) {
            $trimmed = trim($value);
            return $trimmed !== '' ? $trimmed : null;
        }

        if (is_object($value)) {
            $value = (array) $value;
        }

        if (is_array($value)) {
            foreach ($keys as $key) {
                if (isset($value[$key]) && is_string($value[$key]) && trim($value[$key]) !== '') {
                    return trim($value[$key]);
                }
            }
        }

        return null;
    }


    private function normalizeChartSeries(array $payload): array
    {
        $points = $this->extractChartPointList($payload);

        return $this->normalizeChartPointList($points);
    }

    private function extractChartPointList(array $payload): array
    {
        $candidates = [];
        foreach (['chart_data', 'data', 'points', 'chart', 'series', 'results'] as $key) {
            $value = data_get($payload, $key);
            if (is_array($value)) {
                $candidates[] = $value;
            }
        }
        $candidates[] = $payload;

        foreach ($candidates as $candidate) {
            if (! is_array($candidate) || $candidate === []) {
                continue;
            }

            if ($this->isListArray($candidate)) {
                return $candidate;
            }

            foreach ($candidate as $value) {
                if (is_array($value) && $this->isListArray($value)) {
                    return $value;
                }
            }
        }

        return [];
    }

    private function normalizeChartPointList(array $points): array
    {
        $series = [];

        foreach ($points as $point) {
            $normalized = $this->normalizeChartPoint($point);
            if ($normalized !== null) {
                $series[] = $normalized;
            }
        }

        usort($series, fn (array $a, array $b) => $a['x'] <=> $b['x']);

        return [
            'series' => $series,
            'count' => count($series),
        ];
    }

    private function extractSeriesGroups(array $payload): array
    {
        $grouped = [];
        $candidates = [];

        foreach (['series', 'charts', 'chart', 'data', 'chart_data', 'results'] as $key) {
            $value = data_get($payload, $key);
            if (is_array($value)) {
                $candidates[] = $value;
            }
        }

        foreach ($candidates as $candidate) {
            if (! is_array($candidate) || ! $this->isListArray($candidate)) {
                continue;
            }

            foreach ($candidate as $item) {
                if (is_object($item)) {
                    $item = (array) $item;
                }

                if (! is_array($item)) {
                    continue;
                }

                $data = $item['data']
                    ?? $item['points']
                    ?? $item['values']
                    ?? $item['series']
                    ?? null;

                if (! is_array($data) || $data === []) {
                    continue;
                }

                $name = $item['name']
                    ?? $item['label']
                    ?? $item['title']
                    ?? $item['series_name']
                    ?? $item['category']
                    ?? data_get($item, 'license_group_name')
                    ?? data_get($item, 'group_name');

                $key = $name ? $this->normalizeLicenseKey((string) $name) : null;
                if (! $key) {
                    continue;
                }

                $normalized = $this->normalizeChartPointList($data);
                if ($normalized['count'] === 0) {
                    continue;
                }

                $grouped[$key] = $normalized;
            }
        }

        return $grouped;
    }

    private function normalizeChartPoint($point): ?array
    {
        if (is_object($point)) {
            $point = (array) $point;
        }

        if (! is_array($point)) {
            return null;
        }

        $time = null;
        $value = null;

        if ($this->isListArray($point)) {
            $first = $point[0] ?? null;
            $second = $point[1] ?? null;

            if ($this->isLikelyTimestamp($first) && ! $this->isLikelyTimestamp($second)) {
                $time = $first;
                $value = $second;
            } elseif ($this->isLikelyTimestamp($second) && ! $this->isLikelyTimestamp($first)) {
                $time = $second;
                $value = $first;
            } else {
                $time = $first;
                $value = $second;
            }
        } else {
            $time = $point['timestamp']
                ?? $point['time']
                ?? $point['t']
                ?? $point['when']
                ?? $point['date']
                ?? $point['start_time']
                ?? $point['session_start_time']
                ?? $point['race_date']
                ?? null;
            $value = $point['value']
                ?? $point['rating']
                ?? $point['irating']
                ?? $point['i_rating']
                ?? $point['y']
                ?? $point['v']
                ?? null;
        }

        $timestamp = $this->normalizeChartTimestamp($time);
        if ($timestamp === null || $value === null || $value === '') {
            return null;
        }

        return [
            'x' => $timestamp,
            'y' => (int) $value,
        ];
    }

    private function normalizeChartTimestamp($value): ?int
    {
        if ($value instanceof Carbon) {
            return $value->timestamp * 1000;
        }

        if (is_numeric($value)) {
            $numeric = (int) $value;
            $numericString = preg_replace('/\\D+/', '', (string) $value);

            if ($numericString !== '') {
                $length = strlen($numericString);

                if ($length === 8) {
                    $year = (int) substr($numericString, 0, 4);
                    $month = (int) substr($numericString, 4, 2);
                    $day = (int) substr($numericString, 6, 2);
                    if ($year >= 1990 && $year <= 2100 && $month >= 1 && $month <= 12 && $day >= 1 && $day <= 31) {
                        return Carbon::createFromDate($year, $month, $day)->timestamp * 1000;
                    }
                }

                if ($length === 6) {
                    $year = (int) substr($numericString, 0, 4);
                    $month = (int) substr($numericString, 4, 2);
                    if ($year >= 1990 && $year <= 2100 && $month >= 1 && $month <= 12) {
                        return Carbon::createFromDate($year, $month, 1)->timestamp * 1000;
                    }
                }
            }

            if ($numeric > 0 && $numeric < 100_000) {
                return Carbon::createFromTimestamp(0)->addDays($numeric)->timestamp * 1000;
            }

            if ($numeric <= 0) {
                return null;
            }
            return $numeric > 10_000_000_000 ? $numeric : $numeric * 1000;
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === '') {
                return null;
            }

            try {
                return Carbon::parse($trimmed)->timestamp * 1000;
            } catch (\Throwable $exception) {
                return null;
            }
        }

        return null;
    }

    private function isListArray(array $value): bool
    {
        if (function_exists('array_is_list')) {
            return array_is_list($value);
        }

        return array_keys($value) === range(0, count($value) - 1);
    }

    private function isLikelyTimestamp($value): bool
    {
        if ($value instanceof Carbon) {
            return true;
        }

        if (is_numeric($value)) {
            $numeric = (int) $value;
            if ($numeric <= 0) {
                return false;
            }

            return $numeric >= 1_000_000_000;
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === '') {
                return false;
            }

            return (bool) preg_match('/\\d{4}-\\d{2}-\\d{2}/', $trimmed);
        }

        return false;
    }
}
