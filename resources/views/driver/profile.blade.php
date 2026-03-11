@extends('layouts.app')

@section('title', 'Panel piloto | Enosis eSports')
@section('mainClass', 'w-full max-w-6xl mx-auto p-4 md:p-6 space-y-5')

@push('head')
    <style>
        .driver-profile {
            font-family: 'Barlow Condensed', sans-serif;
        }

        .driver-profile h1,
        .driver-profile h2 {
            font-family: 'Orbitron', monospace;
            letter-spacing: 0.04em;
        }

        .driver-profile .text-xs,
        .driver-profile .text-\[11px\] {
            font-family: 'Share Tech Mono', monospace;
            letter-spacing: 0.06em;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
@endpush

@section('content')
    @php
        $totalRaces = $results->count();
        $wins = (int) data_get($stats, 'wins', 0);
        $podiums = (int) data_get($stats, 'podiums', 0);
        $poles = (int) data_get($stats, 'poles', 0);
        $licenseRatings = (array) data_get($stats, 'licenses', []);
        $sportsCar = (array) data_get($licenseRatings, 'sports_car', []);
        $formulaCar = (array) data_get($licenseRatings, 'formula_car', []);
        $oval = (array) data_get($licenseRatings, 'oval', []);
        $dirtRoad = (array) data_get($licenseRatings, 'dirt_road', []);
        $dirtOval = (array) data_get($licenseRatings, 'dirt_oval', []);
        $sportsCarIr = (int) data_get($sportsCar, 'ir', 0);
        $sportsCarSr = (string) data_get($sportsCar, 'sr', '-');
        $sportsCarClass = (string) data_get($sportsCar, 'class', 'R');
        $sportsCarClassColor = (string) data_get($sportsCar, 'class_color', 'border-red-500 text-red-300');
        $sportsCarBorder = (string) data_get($sportsCar, 'class_border', 'border-red-500');
        $sportsCarHex = (string) data_get($sportsCar, 'class_color_hex', '');
        $formulaCarIr = (int) data_get($formulaCar, 'ir', 0);
        $formulaCarSr = (string) data_get($formulaCar, 'sr', '-');
        $formulaCarClass = (string) data_get($formulaCar, 'class', 'R');
        $formulaCarClassColor = (string) data_get($formulaCar, 'class_color', 'border-red-500 text-red-300');
        $formulaCarBorder = (string) data_get($formulaCar, 'class_border', 'border-red-500');
        $formulaCarHex = (string) data_get($formulaCar, 'class_color_hex', '');
        $ovalIr = (int) data_get($oval, 'ir', 0);
        $ovalSr = (string) data_get($oval, 'sr', '-');
        $ovalClass = (string) data_get($oval, 'class', 'R');
        $ovalClassColor = (string) data_get($oval, 'class_color', 'border-red-500 text-red-300');
        $ovalBorder = (string) data_get($oval, 'class_border', 'border-red-500');
        $ovalHex = (string) data_get($oval, 'class_color_hex', '');
        $dirtRoadIr = (int) data_get($dirtRoad, 'ir', 0);
        $dirtRoadSr = (string) data_get($dirtRoad, 'sr', '-');
        $dirtRoadClass = (string) data_get($dirtRoad, 'class', 'R');
        $dirtRoadClassColor = (string) data_get($dirtRoad, 'class_color', 'border-red-500 text-red-300');
        $dirtRoadBorder = (string) data_get($dirtRoad, 'class_border', 'border-red-500');
        $dirtRoadHex = (string) data_get($dirtRoad, 'class_color_hex', '');
        $dirtOvalIr = (int) data_get($dirtOval, 'ir', 0);
        $dirtOvalSr = (string) data_get($dirtOval, 'sr', '-');
        $dirtOvalClass = (string) data_get($dirtOval, 'class', 'R');
        $dirtOvalClassColor = (string) data_get($dirtOval, 'class_color', 'border-red-500 text-red-300');
        $dirtOvalBorder = (string) data_get($dirtOval, 'class_border', 'border-red-500');
        $dirtOvalHex = (string) data_get($dirtOval, 'class_color_hex', '');
        $currentIRating = (int) (data_get($sportsCar, 'ir') ?? data_get($formulaCar, 'ir') ?? data_get($oval, 'ir') ?? data_get($stats, 'irating', 0));
        $safetyRating = (string) (data_get($sportsCar, 'sr') ?? data_get($formulaCar, 'sr') ?? data_get($oval, 'sr') ?? data_get($stats, 'safety_rating', '-'));
        $top5Count = $results->filter(fn ($race) => (int) ($race->finish_position ?? 999) <= 5)->count();
        $winRate = $totalRaces > 0 ? round(($wins / $totalRaces) * 100, 1) : 0;
        $top5Rate = $totalRaces > 0 ? round(($top5Count / $totalRaces) * 100, 1) : 0;
        $avgStart = $results->whereNotNull('starting_position')->avg('starting_position');
        $avgFinish = $results->whereNotNull('finish_position')->avg('finish_position');
        $avgInc = $results->whereNotNull('incidents')->avg('incidents');
        $favoriteTrack = $results->pluck('track_name')->filter()->countBy()->sortDesc()->keys()->first();
        $favoriteSeries = $results->pluck('series_name')->filter()->countBy()->sortDesc()->keys()->first();
        $nameInitial = strtoupper(substr((string) $user->name, 0, 1));
        $memberSince = optional($user->created_at)->format('M Y');
        $lastSyncedValue = data_get($stats, 'last_synced_at');
        $lastSynced = optional($lastSyncedValue)->format('d/m/Y H:i');

        $chartSource = $chartResults ?? $results;

        $buildChart = function ($collection) {
            $collection = $collection->filter(fn ($race) => $race->race_date)->sortBy('race_date')->values();
            $latestRating = (int) ($collection->last()?->newi_rating ?? 0);

            $changes = $collection
                ->pluck('irating_change')
                ->map(fn ($value) => (int) ($value ?? 0))
                ->values();

            if ($changes->isEmpty()) {
                $historyValues = collect([$latestRating > 0 ? $latestRating : 1500]);
            } else {
                $base = ($latestRating > 0 ? $latestRating : 1500) - $changes->sum();
                $running = $base;
                $historyValues = collect([$running]);

                foreach ($changes as $change) {
                    $running += $change;
                    $historyValues->push($running);
                }
            }

            $datePoints = $collection->pluck('race_date')->values();
            if ($datePoints->count() === $historyValues->count() - 1 && $datePoints->isNotEmpty()) {
                $datePoints->prepend($datePoints->first());
            }

            $series = [];
            foreach ($historyValues->values() as $index => $value) {
                $date = $datePoints[$index] ?? $datePoints->first();
                $timestamp = $date?->timestamp ? $date->timestamp * 1000 : now()->timestamp * 1000;
                $series[] = ['x' => $timestamp, 'y' => (int) $value];
            }

            return [
                'series' => $series,
                'count' => count($series),
            ];
        };

        $sportsCarResults = $chartSource->where('license_key', 'sports_car');
        $formulaCarResults = $chartSource->where('license_key', 'formula_car');
        $ovalResults = $chartSource->where('license_key', 'oval');
        $dirtRoadResults = $chartSource->where('license_key', 'dirt_road');
        $dirtOvalResults = $chartSource->where('license_key', 'dirt_oval');
        $roadResults = $chartSource->where('license_key', 'road');

        $chartSeries = [
            'sports_car' => $buildChart($sportsCarResults->isEmpty() ? $chartSource : $sportsCarResults),
            'formula_car' => $buildChart($formulaCarResults->isEmpty() ? $chartSource : $formulaCarResults),
            'oval' => $buildChart($ovalResults->isEmpty() ? $chartSource : $ovalResults),
            'dirt_road' => $buildChart($dirtRoadResults->isEmpty() ? $chartSource : $dirtRoadResults),
            'dirt_oval' => $buildChart($dirtOvalResults->isEmpty() ? $chartSource : $dirtOvalResults),
            'road' => $buildChart($roadResults->isEmpty() ? $chartSource : $roadResults),
        ];

        $activeSeries = 'sports_car';
        $activeChart = $chartSeries[$activeSeries];
        $chartCount = $activeChart['count'];
    @endphp
    <div class="driver-profile space-y-5">

    @if(session('status'))
        <section class="bg-green-700/80 border border-green-500 rounded-lg p-3 text-sm">{{ session('status') }}</section>
    @endif

@if($errors->any())
    <section class="bg-red-700/80 border border-red-500 rounded-lg p-3 text-sm">{{ $errors->first() }}</section>
@endif

@if(!empty($iracingNotice))
    <section class="bg-amber-700/70 border border-amber-500 rounded-lg p-3 text-sm">{{ $iracingNotice }}</section>
@endif

<section class="rounded-2xl border border-[#7a0007]/60 bg-[#000000]/75 p-4 md:p-6 shadow-lg">
        <div class="grid gap-5 lg:grid-cols-12">
            <div class="lg:col-span-7 space-y-4 text-center sm:text-left">
                <div class="flex flex-col items-center sm:flex-row sm:items-center gap-4">
                    <div class="w-24 h-24 rounded-xl border-2 border-[#e8000d] bg-black/50 flex items-center justify-center text-4xl font-black text-white">
                        {{ $nameInitial }}
                    </div>
                    <div class="space-y-1">
                        <h1 class="text-2xl md:text-3xl font-black uppercase tracking-wide text-[#e8000d]">{{ $user->name }}</h1>
                        <p class="text-sm text-zinc-300">{{ $user->nickname ? '@'.$user->nickname : '@driver' }} | Enosis eSports driver</p>
                        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 text-xs">
                            <span class="rounded-full border border-zinc-600 bg-zinc-900 px-2 py-1 text-zinc-300">ID #{{ $user->iracing_customer_id ?: '-' }}</span>
                            <span class="rounded-full border border-zinc-600 bg-zinc-900 px-2 py-1 text-zinc-300">{{ $memberSince ? 'Since '.$memberSince : 'Member' }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap justify-center sm:justify-start gap-2">
                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-full border border-zinc-600 bg-zinc-900 text-[11px] text-zinc-300">X</span>
                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-full border border-zinc-600 bg-zinc-900 text-[11px] text-zinc-300">IG</span>
                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-full border border-zinc-600 bg-zinc-900 text-[11px] text-zinc-300">YT</span>
                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-full border border-zinc-600 bg-zinc-900 text-[11px] text-zinc-300">TW</span>
                </div>

                <div class="flex flex-col sm:flex-row gap-2">

                    @if(!$user->iracing_linked)
                        <a href="{{ route('iracing.oauth.redirect') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-sm font-semibold">
                            Vincular cuenta iRacing
                        </a>
                    @else
                        <span class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 rounded-lg border border-emerald-500/50 bg-emerald-500/10 text-emerald-300 text-sm font-semibold">
                            Cuenta vinculada
                        </span>
                        <form action="{{ route('iracing.oauth.unlink') }}" method="POST" class="w-full sm:w-auto">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2 rounded-lg border border-red-500/60 text-red-300 hover:bg-red-600/20 text-sm font-semibold">
                                Desvincular
                            </button>
                        </form>
                    @endif

                    <form action="{{ route('logout') }}" method="POST" class="w-full sm:w-auto">
                        @csrf
                        <button type="submit" class="w-full px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-sm font-semibold">Cerrar sesión</button>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-5 rounded-xl border border-zinc-700/50 bg-[#101010]/85 p-3 text-center sm:text-left">
                <div class="mb-3">
                    <p class="text-lg font-bold text-white">{{ $user->name }}</p>
                    <p class="text-xs text-zinc-400">{{ $user->iracing_customer_id ? '#'.$user->iracing_customer_id : 'No hay id de cliente' }}</p>
                </div>

                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div class="rounded-lg border {{ $sportsCarBorder }} bg-zinc-900/80 p-3" style="{{ $sportsCarHex ? 'border-color: '.$sportsCarHex.';' : '' }}">
                        <p class="text-zinc-300">Sports Car</p>
                        <div class="mt-1.5 flex items-center justify-center sm:justify-start gap-2">
                            <span class="rounded border px-1.5 py-0.5 {{ $sportsCarClassColor }}" style="{{ $sportsCarHex ? 'border-color: '.$sportsCarHex.'; color: '.$sportsCarHex.';' : '' }}">{{ $sportsCarClass }} {{ $sportsCarSr }}</span>
                            <span class="rounded border border-[#fc0706] px-1.5 py-0.5 text-[#fc0706]">{{ $sportsCarIr > 0 ? number_format($sportsCarIr) : '-' }} iR</span>
                        </div>
                    </div>
                    <div class="rounded-lg border {{ $formulaCarBorder }} bg-zinc-900/80 p-3" style="{{ $formulaCarHex ? 'border-color: '.$formulaCarHex.';' : '' }}">
                        <p class="text-zinc-300">Formula Car</p>
                        <div class="mt-1.5 flex items-center justify-center sm:justify-start gap-2">
                            <span class="rounded border px-1.5 py-0.5 {{ $formulaCarClassColor }}" style="{{ $formulaCarHex ? 'border-color: '.$formulaCarHex.'; color: '.$formulaCarHex.';' : '' }}">{{ $formulaCarClass }} {{ $formulaCarSr }}</span>
                            <span class="rounded border border-[#fc0706] px-1.5 py-0.5 text-[#fc0706]">{{ $formulaCarIr > 0 ? number_format($formulaCarIr) : '-' }} iR</span>
                        </div>
                    </div>
                    <div class="rounded-lg border {{ $ovalBorder }} bg-zinc-900/80 p-3" style="{{ $ovalHex ? 'border-color: '.$ovalHex.';' : '' }}">
                        <p class="text-zinc-300">Oval</p>
                        <div class="mt-1.5 flex items-center justify-center sm:justify-start gap-2">
                            <span class="rounded border px-1.5 py-0.5 {{ $ovalClassColor }}" style="{{ $ovalHex ? 'border-color: '.$ovalHex.'; color: '.$ovalHex.';' : '' }}">{{ $ovalClass }} {{ $ovalSr }}</span>
                            <span class="rounded border border-[#fc0706] px-1.5 py-0.5 text-[#fc0706]">{{ $ovalIr > 0 ? number_format($ovalIr) : '-' }} iR</span>
                        </div>
                    </div>
                    <div class="rounded-lg border {{ $dirtRoadBorder }} bg-zinc-900/80 p-3" style="{{ $dirtRoadHex ? 'border-color: '.$dirtRoadHex.';' : '' }}">
                        <p class="text-zinc-300">Dirt Road</p>
                        <div class="mt-1.5 flex items-center justify-center sm:justify-start gap-2">
                            <span class="rounded border px-1.5 py-0.5 {{ $dirtRoadClassColor }}" style="{{ $dirtRoadHex ? 'border-color: '.$dirtRoadHex.'; color: '.$dirtRoadHex.';' : '' }}">{{ $dirtRoadClass }} {{ $dirtRoadSr }}</span>
                            <span class="rounded border border-[#fc0706] px-1.5 py-0.5 text-[#fc0706]">{{ $dirtRoadIr > 0 ? number_format($dirtRoadIr) : '-' }} iR</span>
                        </div>
                    </div>
                    <div class="rounded-lg border {{ $dirtOvalBorder }} bg-zinc-900/80 p-3" style="{{ $dirtOvalHex ? 'border-color: '.$dirtOvalHex.';' : '' }}">
                        <p class="text-zinc-300">Dirt Oval</p>
                        <div class="mt-1.5 flex items-center justify-center sm:justify-start gap-2">
                            <span class="rounded border px-1.5 py-0.5 {{ $dirtOvalClassColor }}" style="{{ $dirtOvalHex ? 'border-color: '.$dirtOvalHex.'; color: '.$dirtOvalHex.';' : '' }}">{{ $dirtOvalClass }} {{ $dirtOvalSr }}</span>
                            <span class="rounded border border-[#fc0706] px-1.5 py-0.5 text-[#fc0706]">{{ $dirtOvalIr > 0 ? number_format($dirtOvalIr) : '-' }} iR</span>
                        </div>
                    </div>
                </div>

                <p class="mt-3 text-[11px] text-zinc-400"> Última actualización: {{ $lastSynced ?: '-' }}</p>
            </div>
        </div>
    </section>

    <section class="rounded-2xl border border-zinc-700/60 bg-[#000000]/75 p-4 md:p-6 shadow-lg">
        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-xl font-bold text-[#e8000d]">Historial de iRating</h2>
            <div class="flex flex-wrap gap-2 text-[11px] uppercase tracking-wide">
                <button type="button" data-series-btn="sports_car" class="rounded-full border border-[#e8000d]/80 bg-[#7a0007]/25 px-3 py-1 text-[#e8000d]">Sports Car</button>
                <button type="button" data-series-btn="formula_car" class="rounded-full border border-zinc-600 px-3 py-1 text-zinc-400">Formula Car</button>
                <button type="button" data-series-btn="oval" class="rounded-full border border-zinc-600 px-3 py-1 text-zinc-400">Oval</button>
                <button type="button" data-series-btn="dirt_oval" class="rounded-full border border-zinc-600 px-3 py-1 text-zinc-400">Dirt Oval</button>
                <button type="button" data-series-btn="dirt_road" class="rounded-full border border-zinc-600 px-3 py-1 text-zinc-400">Dirt Road</button>
                <button type="button" data-series-btn="road" class="rounded-full border border-zinc-600 px-3 py-1 text-zinc-400">Road (Retirado)</button>
            </div>
        </div>

        <div id="performanceChart" data-series='@json($chartSeries)' class="rounded-xl border border-zinc-700/50 bg-[#101010]/85 p-3">
            <div id="historyChart" class="h-64 w-full"></div>
            <div id="historyEmpty" class="flex h-64 items-center justify-center text-zinc-400">
                Cargando gráfico...
            </div>
        </div>
    </section>

    <script>
        (() => {
            const chart = document.getElementById('performanceChart');
            if (!chart) return;

            const series = JSON.parse(chart.dataset.series || '{}');
            const empty = document.getElementById('historyEmpty');
            const chartContainer = document.getElementById('historyChart');
            const buttons = document.querySelectorAll('[data-series-btn]');

            if (!chartContainer) return;
            if (typeof ApexCharts === 'undefined') {
                empty.classList.remove('hidden');
                empty.textContent = 'No se pudo cargar ApexCharts.';
                return;
            }

            const baseOptions = {
                chart: {
                    type: 'area',
                    height: 260,
                    toolbar: { show: false },
                    zoom: { enabled: false },
                    animations: { speed: 450 },
                    foreColor: '#a1a1aa',
                },
                stroke: { curve: 'smooth', width: 3 },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.35,
                        opacityTo: 0.0,
                        stops: [0, 100],
                    },
                },
                colors: ['#f2b310'],
                grid: {
                    borderColor: 'rgba(255,255,255,0.06)',
                    strokeDashArray: 4,
                    xaxis: { lines: { show: true } },
                    yaxis: { lines: { show: true } },
                },
                xaxis: {
                    type: 'datetime',
                    labels: { datetimeUTC: false, format: 'MMM d' },
                    tickAmount: 10,
                },
                yaxis: {
                    tickAmount: 5,
                    labels: {
                        formatter: (value) => Math.round(value),
                    },
                },
                tooltip: {
                    x: { format: 'dd MMM yyyy' },
                },
                dataLabels: { enabled: false },
            };

            const initialSeries = (series && series.sports_car && series.sports_car.series) ? series.sports_car.series : [];
            const chart = new ApexCharts(chartContainer, {
                ...baseOptions,
                series: [{ name: 'iRating', data: initialSeries }],
            });
            chart.render();

            const setActive = (key) => {
                const data = series[key];
                if (!data) return;

                const seriesData = data.series ?? [];
                const hasData = seriesData.length > 0;
                empty.classList.toggle('hidden', hasData);
                chartContainer.classList.toggle('hidden', !hasData);
                empty.textContent = hasData ? '' : 'No hay suficientes carreras para mostrar historial.';
                chart.updateSeries([{ name: 'iRating', data: seriesData }], true);

                buttons.forEach((btn) => {
                    const isActive = btn.dataset.seriesBtn === key;
                    btn.classList.toggle('border-[#e8000d]/80', isActive);
                    btn.classList.toggle('bg-[#7a0007]/25', isActive);
                    btn.classList.toggle('text-[#e8000d]', isActive);
                    btn.classList.toggle('border-zinc-600', !isActive);
                    btn.classList.toggle('text-zinc-400', !isActive);
                });
            };

            buttons.forEach((btn) => {
                btn.addEventListener('click', () => setActive(btn.dataset.seriesBtn));
            });

            setActive('sports_car');
        })();
    </script>

    <div class="grid gap-5 lg:grid-cols-2">
        <section class="rounded-2xl border border-zinc-700/60 bg-[#000000]/75 p-4 md:p-6 shadow-lg">
            <h2 class="mb-4 text-3xl font-bold text-[#e8000d]">Estadísticas generales</h2>

            <div class="grid grid-cols-3 gap-2 text-center">
                <div class="rounded-lg border border-zinc-700 bg-[#101010] p-3">
                    <p class="text-[11px] text-zinc-400">Victorias</p>
                    <p class="mt-1 text-2xl font-black text-white">{{ $wins }}</p>
                </div>
                <div class="rounded-lg border border-zinc-700 bg-[#101010] p-3">
                    <p class="text-[11px] text-zinc-400">Top 5</p>
                    <p class="mt-1 text-2xl font-black text-white">{{ $top5Count }}</p>
                </div>
                <div class="rounded-lg border border-zinc-700 bg-[#101010] p-3">
                    <p class="text-[11px] text-zinc-400">Poles</p>
                    <p class="mt-1 text-2xl font-black text-white">{{ $poles }}</p>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-2 text-sm">
                <div class="rounded-lg bg-[#101010] px-3 py-2 flex items-center justify-between border border-zinc-700/60">
                    <span class="text-zinc-400">Posición en parrilla promedio</span>
                    <span class="font-semibold text-white">{{ $avgStart !== null ? number_format($avgStart, 1) : '-' }}</span>
                </div>
                <div class="rounded-lg bg-[#101010] px-3 py-2 flex items-center justify-between border border-zinc-700/60">
                    <span class="text-zinc-400">Posición final promedio</span>
                    <span class="font-semibold text-white">{{ $avgFinish !== null ? number_format($avgFinish, 1) : '-' }}</span>
                </div>
                <div class="rounded-lg bg-[#101010] px-3 py-2 flex items-center justify-between border border-zinc-700/60">
                    <span class="text-zinc-400">Incidentes promedio</span>
                    <span class="font-semibold text-white">{{ $avgInc !== null ? number_format($avgInc, 2) : '-' }}</span>
                </div>
                <div class="rounded-lg bg-[#101010] px-3 py-2 flex items-center justify-between border border-zinc-700/60">
                    <span class="text-zinc-400">Carreras totales</span>
                    <span class="font-semibold text-white">{{ $totalRaces }}</span>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-zinc-700/60 bg-[#000000]/75 p-4 md:p-6 shadow-lg">
            <h2 class="mb-4 text-3xl font-bold text-[#e8000d]">Estadísticas 2026</h2>

            <div class="grid gap-2">
                <div class="rounded-lg bg-[#101010] border border-zinc-700/60 px-3 py-2 flex items-center justify-between">
                    <span class="text-zinc-400 text-sm">Carreras</span>
                    <span class="text-white font-bold">{{ $totalRaces }}</span>
                </div>
                <div class="rounded-lg bg-[#101010] border border-zinc-700/60 px-3 py-2 flex items-center justify-between">
                    <span class="text-zinc-400 text-sm">Victorias</span>
                    <span class="text-[#e8000d] font-bold">{{ number_format($winRate, 1) }}%</span>
                </div>
                <div class="rounded-lg bg-[#101010] border border-zinc-700/60 px-3 py-2 flex items-center justify-between">
                    <span class="text-zinc-400 text-sm">Top 5</span>
                    <span class="text-sky-300 font-bold">{{ number_format($top5Rate, 1) }}%</span>
                </div>
                <div class="rounded-lg bg-[#101010] border border-zinc-700/60 px-3 py-2 flex items-center justify-between">
                    <span class="text-zinc-400 text-sm">Serie favorita</span>
                    <span class="text-white font-semibold text-right">{{ $favoriteSeries ?: '-' }}</span>
                </div>
                <div class="rounded-lg bg-[#101010] border border-zinc-700/60 px-3 py-2 flex items-center justify-between">
                    <span class="text-zinc-400 text-sm">Circuito favorito</span>
                    <span class="text-white font-semibold text-right">{{ $favoriteTrack ?: '-' }}</span>
                </div>
            </div>
        </section>
    </div>

    <section class="rounded-2xl border border-zinc-700/60 bg-[#000000]/75 p-4 md:p-6 shadow-lg">
        <h2 class="mb-4 text-3xl font-bold text-[#e8000d]">Últimas carreras</h2>

        @if($user->iracing_linked)
            <div class="space-y-3">
                @forelse($results as $race)
                    @php
                        $iDelta = (int) ($race->irating_change ?? 0);
                    @endphp
                    <article class="rounded-xl border border-zinc-600/70 bg-[#222222]/70">
                        <div class="flex items-start justify-between gap-3 px-3 py-3 border-b border-zinc-700/60">
                            <div>
                                <p class="text-lg font-bold text-white">{{ $race->series_name ?? 'iRacing Series' }}</p>
                                <p class="text-sm text-zinc-300">{{ optional($race->race_date)->format('M d, Y') ?: '-' }}</p>
                                <p class="mt-1 text-sm text-zinc-200">{{ $race->track_name ?? 'Unknown track' }}</p>
                            </div>
                            <div class="rounded-lg border border-zinc-500 bg-zinc-800/50 px-3 py-1.5 text-lg font-black text-white">
                                P{{ $race->finish_position ?? '-' }}
                            </div>
                        </div>

                        <div class="grid gap-2 p-3 md:grid-cols-4">
                            <div class="rounded-md bg-zinc-700/50 px-2 py-2">
                                <p class="text-xs text-zinc-300">iRating</p>
                                <p class="font-bold text-white">
                                    {{ $currentIRating > 0 ? number_format($currentIRating) : '-' }}
                                    <span class="{{ $iDelta >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                                        {{ $iDelta >= 0 ? '+' : '' }}{{ $iDelta }}
                                    </span>
                                </p>
                            </div>
                            <div class="rounded-md bg-zinc-700/50 px-2 py-2">
                                <p class="text-xs text-zinc-300">Incidentes</p>
                                <p class="font-bold text-white">{{ $race->incidents ?? '-' }}</p>
                            </div>
                            <div class="rounded-md bg-zinc-700/50 px-2 py-2">
                                <p class="text-xs text-zinc-300">Parrilla / Meta</p>
                                <p class="font-bold text-white">P{{ $race->starting_position ?? '-' }} / P{{ $race->finish_position ?? '-' }}</p>
                            </div>
                            <div class="rounded-md bg-zinc-700/50 px-2 py-2">
                                <p class="text-xs text-zinc-300">Subsession</p>
                                <p class="font-bold text-white">{{ $race->subsession_id ?? '-' }}</p>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-xl border border-zinc-700/50 bg-[#0d0f1b]/85 p-4 text-zinc-300">Todavia no hay carreras cacheadas.</div>
                @endforelse
            </div>
        @else
            <div class="rounded-xl border border-zinc-700/50 bg-[#101010]/85 p-4 text-zinc-300">
                Sin vincular iRacing no se cargaran carreras y estadisticas.
            </div>
        @endif
    </section>
    </div>
@endsection
