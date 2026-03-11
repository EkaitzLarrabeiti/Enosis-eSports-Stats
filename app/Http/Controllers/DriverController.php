<?php

namespace App\Http\Controllers;

use App\Services\IRacingApiService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
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
                $memberInfo = $this->iracingApiService->getForUser($user, 'data/member/info');
                $summary = $this->iracingApiService->getForUser($user, 'data/stats/member_summary');
                $recentRaces = $this->iracingApiService->getForUser($user, 'data/stats/member_recent_races');

                $allResults = $this->mapRecentRaces(collect(data_get($recentRaces, 'races', [])));
                $results = $allResults->take(10)->values();
                $stats = $this->buildLiveStats($summary, $results, $memberInfo);
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
            'iracingNotice' => $iracingNotice,
        ]);
    }

    private function buildLiveStats(array $summary, Collection $results, ?array $licensePayload): array
    {
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

    private function mapRecentRaces(Collection $races): Collection
    {
        return $races->map(function (array $race) {
            $startTime = data_get($race, 'session_start_time');
            $raceDate = $startTime ? Carbon::parse($startTime) : null;
            $oldRating = (int) data_get($race, 'oldi_rating', 0);
            $newRating = (int) data_get($race, 'newi_rating', 0);
            $categoryId = data_get($race, 'license_category_id')
                ?? data_get($race, 'category_id')
                ?? data_get($race, 'license_category');
            $groupId = data_get($race, 'license_group_id') ?? data_get($race, 'group_id');
            $groupName = data_get($race, 'license_group_name')
                ?? data_get($race, 'license_group')
                ?? data_get($race, 'group_name')
                ?? data_get($race, 'category');
            $licenseKey = $this->resolveRaceLicenseKey($groupName, $categoryId, $groupId);

            return (object) [
                'series_name' => data_get($race, 'series_name'),
                'race_date' => $raceDate,
                'track_name' => data_get($race, 'track.track_name'),
                'finish_position' => data_get($race, 'finish_position'),
                'starting_position' => data_get($race, 'start_position'),
                'incidents' => data_get($race, 'incidents'),
                'subsession_id' => data_get($race, 'subsession_id'),
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
            2 => 'sports_car',
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

        return match ((int) $categoryId) {
            1 => 'oval',
            3 => 'dirt_oval',
            4 => 'dirt_road',
            2 => 'road',
            default => 'road',
        };
    }
}
