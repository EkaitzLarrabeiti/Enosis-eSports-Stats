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
@endpush

@section('content')
    @php
        $totalRaces = $results->count();
        $wins = (int) data_get($stats, 'wins', 0);
        $podiums = (int) data_get($stats, 'podiums', 0);
        $poles = (int) data_get($stats, 'poles', 0);
        $currentIRating = (int) data_get($stats, 'irating', 0);
        $safetyRating = (string) data_get($stats, 'safety_rating', '-');
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

        $changes = $results
            ->sortBy('race_date')
            ->pluck('irating_change')
            ->map(fn ($value) => (int) ($value ?? 0))
            ->values();

        if ($changes->isEmpty()) {
            $historyValues = collect([$currentIRating > 0 ? $currentIRating : 1500]);
        } else {
            $base = ($currentIRating > 0 ? $currentIRating : 1500) - $changes->sum();
            $running = $base;
            $historyValues = collect([$running]);

            foreach ($changes as $change) {
                $running += $change;
                $historyValues->push($running);
            }
        }

        $chartValues = $historyValues->values()->all();
        $chartCount = count($chartValues);
        $chartMin = $chartCount > 0 ? min($chartValues) : 0;
        $chartMax = $chartCount > 0 ? max($chartValues) : 1;
        $chartRange = max(1, $chartMax - $chartMin);
        $chartWidth = 1000;
        $chartHeight = 220;

        $points = [];
        foreach ($chartValues as $index => $value) {
            $x = $chartCount > 1 ? ($index / ($chartCount - 1)) * $chartWidth : 0;
            $normalized = ($value - $chartMin) / $chartRange;
            $y = $chartHeight - ($normalized * $chartHeight);
            $points[] = round($x, 2).','.round($y, 2);
        }

        $chartPoints = implode(' ', $points);
        $chartArea = $chartCount > 0 ? $chartPoints.' '.$chartWidth.','.$chartHeight.' 0,'.$chartHeight : '';
    @endphp
    <div class="driver-profile space-y-5">

    @if(session('status'))
        <section class="bg-green-700/80 border border-green-500 rounded-lg p-3 text-sm">{{ session('status') }}</section>
    @endif

    @if($errors->any())
        <section class="bg-red-700/80 border border-red-500 rounded-lg p-3 text-sm">{{ $errors->first() }}</section>
    @endif

    <section class="rounded-2xl border border-[#7a0007]/60 bg-[#000000]/85 p-4 md:p-6 shadow-lg">
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
                    @endif

                    <form action="{{ route('logout') }}" method="POST" class="w-full sm:w-auto">
                        @csrf
                        <button type="submit" class="w-full px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-sm font-semibold">Logout</button>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-5 rounded-xl border border-zinc-700/50 bg-[#101010]/85 p-3 text-center sm:text-left">
                <div class="mb-3">
                    <p class="text-lg font-bold text-white">{{ $user->name }}</p>
                    <p class="text-xs text-zinc-400">{{ $user->iracing_customer_id ? '#'.$user->iracing_customer_id : 'No customer id yet' }}</p>
                </div>

                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div class="rounded-lg border border-[#7a0007]/80 bg-zinc-900/80 p-2">
                        <p class="text-zinc-400">Sports Car</p>
                        <div class="mt-1 flex items-center justify-center sm:justify-start gap-2">
                            <span class="rounded border border-red-500 px-1.5 py-0.5 text-red-300">R {{ $safetyRating }}</span>
                            <span class="rounded border border-[#e8000d] px-1.5 py-0.5 text-[#e8000d]">{{ $currentIRating > 0 ? number_format($currentIRating) : '-' }} iR</span>
                        </div>
                    </div>
                    <div class="rounded-lg border border-[#7a0007]/80 bg-zinc-900/80 p-2">
                        <p class="text-zinc-400">Formula Car</p>
                        <div class="mt-1 flex items-center justify-center sm:justify-start gap-2">
                            <span class="rounded border border-blue-500 px-1.5 py-0.5 text-blue-300">A {{ $safetyRating }}</span>
                            <span class="rounded border border-[#e8000d] px-1.5 py-0.5 text-[#e8000d]">{{ $currentIRating > 0 ? number_format($currentIRating) : '-' }} iR</span>
                        </div>
                    </div>
                    <div class="rounded-lg border border-[#7a0007]/80 bg-zinc-900/80 p-2">
                        <p class="text-zinc-400">Oval</p>
                        <div class="mt-1 flex items-center justify-center sm:justify-start gap-2">
                            <span class="rounded border border-red-500 px-1.5 py-0.5 text-red-300">R {{ $safetyRating }}</span>
                            <span class="rounded border border-[#e8000d] px-1.5 py-0.5 text-[#e8000d]">{{ $currentIRating > 0 ? number_format($currentIRating) : '-' }} iR</span>
                        </div>
                    </div>
                    <div class="rounded-lg border border-[#7a0007]/80 bg-zinc-900/80 p-2">
                        <p class="text-zinc-400">Dirt Road</p>
                        <div class="mt-1 flex items-center justify-center sm:justify-start gap-2">
                            <span class="rounded border border-red-500 px-1.5 py-0.5 text-red-300">R {{ $safetyRating }}</span>
                            <span class="rounded border border-[#e8000d] px-1.5 py-0.5 text-[#e8000d]">{{ $currentIRating > 0 ? number_format($currentIRating) : '-' }} iR</span>
                        </div>
                    </div>
                    <div class="rounded-lg border border-[#7a0007]/80 bg-zinc-900/80 p-2">
                        <p class="text-zinc-400">Dirt Oval</p>
                        <div class="mt-1 flex items-center justify-center sm:justify-start gap-2">
                            <span class="rounded border border-red-500 px-1.5 py-0.5 text-red-300">R {{ $safetyRating }}</span>
                            <span class="rounded border border-[#e8000d] px-1.5 py-0.5 text-[#e8000d]">{{ $currentIRating > 0 ? number_format($currentIRating) : '-' }} iR</span>
                        </div>
                    </div>
                </div>

                <p class="mt-3 text-[11px] text-zinc-400"> Última actualización: {{ $lastSynced ?: '-' }}</p>
            </div>
        </div>
    </section>

    <section class="rounded-2xl border border-zinc-700/60 bg-[#000000]/85 p-4 md:p-6 shadow-lg">
        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-xl font-bold text-[#e8000d]">Historial de rendimiento</h2>
            <div class="flex flex-wrap gap-2 text-[11px] uppercase tracking-wide">
                <span class="rounded-full border border-[#e8000d]/80 bg-[#7a0007]/25 px-3 py-1 text-[#e8000d]">Sports Car</span>
                <span class="rounded-full border border-zinc-600 px-3 py-1 text-zinc-400">Formula Car</span>
                <span class="rounded-full border border-zinc-600 px-3 py-1 text-zinc-400">Oval</span>
                <span class="rounded-full border border-zinc-600 px-3 py-1 text-zinc-400">Dirt Oval</span>
                <span class="rounded-full border border-zinc-600 px-3 py-1 text-zinc-400">Dirt Road</span>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-700/50 bg-[#101010]/85 p-3">
            @if($chartCount > 1)
                <svg viewBox="0 0 1000 220" class="h-64 w-full">
                    <defs>
                        <linearGradient id="historyFill" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#e8000d" stop-opacity="0.35"/>
                            <stop offset="100%" stop-color="#7a0007" stop-opacity="0"/>
                        </linearGradient>
                    </defs>
                    <rect x="0" y="0" width="1000" height="220" fill="#101010"/>
                    <path d="M {{ $chartArea }}" fill="url(#historyFill)"></path>
                    <polyline points="{{ $chartPoints }}" fill="none" stroke="#e8000d" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"></polyline>
                </svg>
            @else
                <div class="flex h-64 items-center justify-center text-zinc-400">No hay suficientes carreras para mostrar historial.</div>
            @endif
        </div>
    </section>

    <div class="grid gap-5 lg:grid-cols-2">
        <section class="rounded-2xl border border-zinc-700/60 bg-[#000000]/85 p-4 md:p-6 shadow-lg">
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

        <section class="rounded-2xl border border-zinc-700/60 bg-[#000000]/85 p-4 md:p-6 shadow-lg">
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

    <section class="rounded-2xl border border-zinc-700/60 bg-[#000000]/85 p-4 md:p-6 shadow-lg">
        <h2 class="mb-4 text-3xl font-bold text-[#e8000d]">Últimas carreras</h2>

        @if($user->iracing_linked)
            <div class="space-y-3">
                @forelse($results as $race)
                    @php
                        $iDelta = (int) ($race->irating_change ?? 0);
                    @endphp
                    <article class="rounded-xl border border-zinc-600/70 bg-[#0d0f1b]/85">
                        <div class="flex items-start justify-between gap-3 px-3 py-3 border-b border-zinc-700/60">
                            <div>
                                <p class="text-lg font-bold text-white">{{ $race->series_name ?? 'iRacing Series' }}</p>
                                <p class="text-sm text-zinc-300">{{ optional($race->race_date)->format('M d, Y') ?: '-' }}</p>
                                <p class="mt-1 text-sm text-zinc-200">{{ $race->track_name ?? 'Unknown track' }}</p>
                            </div>
                            <div class="rounded-lg border border-zinc-500 bg-zinc-800/80 px-3 py-1.5 text-lg font-black text-white">
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
