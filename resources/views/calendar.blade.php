@extends('layouts.app')

@section('title', 'Calendario | Enosis eSports Stats')
@section('bodyClass', 'calendar-body bg-black min-h-screen relative text-white')
@section('mainClass', 'w-full max-w-6xl mx-auto p-4 md:p-6 space-y-6')

@push('head')
    <style>
        .calendar-body header {
            background-color: rgba(0, 0, 0, 0.18);
            border-color: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
        }

        .calendar-page {
            font-family: 'Barlow Condensed', sans-serif;
        }

        .calendar-page h1,
        .calendar-page h2,
        .calendar-page h3 {
            font-family: 'Orbitron', monospace;
            letter-spacing: 0.04em;
        }

        .calendar-page .text-xs,
        .calendar-page .text-\[11px\] {
            font-family: 'Share Tech Mono', monospace;
            letter-spacing: 0.06em;
        }

        .calendar-card {
            position: relative;
            overflow: hidden;
        }

        .calendar-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(120% 120% at 0% 0%, rgba(232, 0, 13, 0.18), transparent 55%),
                radial-gradient(120% 120% at 100% 0%, rgba(94, 94, 120, 0.22), transparent 60%);
            opacity: 0.9;
            pointer-events: none;
        }

        .calendar-card > * {
            position: relative;
        }

        .calendar-week-row {
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 0.75rem;
            align-items: center;
        }
    </style>
@endpush

@section('content')
    <div class="calendar-page space-y-6">
        <section class="rounded-2xl border border-zinc-700/60 bg-[#000000]/70 p-5 md:p-6 shadow-lg">
            <div class="rounded-2xl border border-zinc-800/70 bg-[#0f1015]/80 p-4 shadow-inner">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex flex-wrap gap-2">
                        <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-zinc-700/70 bg-[#14151c]/90 px-4 py-2 text-sm font-semibold text-zinc-200">

                            Favorites
                        </button>
                        <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-zinc-700/70 bg-[#14151c]/90 px-4 py-2 text-sm font-semibold text-zinc-200">

                            Hidden
                        </button>
                    </div>

                    <div class="flex flex-1 flex-col gap-3 lg:flex-row lg:items-center lg:justify-center">
                        <label class="flex w-full max-w-md items-center rounded-xl border border-zinc-700/70 bg-[#14151c]/90 px-4 py-2 text-sm text-zinc-300">
                            <span class="mr-2 text-zinc-500">Search</span>
                            <input type="text" placeholder="Search by serie name or car..." class="w-full bg-transparent text-sm text-white placeholder:text-zinc-500 focus:outline-none">
                        </label>
                        <div class="flex items-center gap-2 text-sm text-zinc-300">
                            <span class="text-zinc-400">Order by</span>
                            <select class="rounded-xl border border-zinc-700/70 bg-[#14151c]/90 px-3 py-2 text-sm text-white focus:outline-none">
                                <option>Class</option>
                                <option>Start time</option>
                                <option>Series name</option>
                            </select>
                        </div>
                    </div>

                    <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-zinc-700/70 bg-[#14151c]/90 px-4 py-2 text-sm font-semibold text-zinc-200">

                        Reset
                    </button>
                </div>

                <div class="mt-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex flex-wrap gap-2">
                        <span class="inline-flex items-center gap-2 rounded-xl bg-[#f2b310] px-4 py-1.5 text-sm font-semibold text-black">
                            Sports car
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-xl bg-[#f2b310] px-4 py-1.5 text-sm font-semibold text-black">
                            Formula car
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-xl border border-zinc-700/70 bg-[#14151c]/90 px-4 py-1.5 text-sm font-semibold text-zinc-300">
                            Oval
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-xl border border-zinc-700/70 bg-[#14151c]/90 px-4 py-1.5 text-sm font-semibold text-zinc-300">
                            Dirt oval
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-xl border border-zinc-700/70 bg-[#14151c]/90 px-4 py-1.5 text-sm font-semibold text-zinc-300">
                            Dirt road
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-xl border border-zinc-700/70 bg-[#14151c]/90 px-4 py-1.5 text-sm font-semibold text-zinc-300">
                            Endurance
                        </span>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <span class="rounded-xl bg-[#520000] px-4 py-1.5 text-sm font-semibold text-red-300">Rookie</span>
                        <span class="rounded-xl bg-[#5a2b00] px-4 py-1.5 text-sm font-semibold text-amber-300">Class D</span>
                        <span class="rounded-xl bg-[#5a4a00] px-4 py-1.5 text-sm font-semibold text-yellow-300">Class C</span>
                        <span class="rounded-xl bg-[#1f4f00] px-4 py-1.5 text-sm font-semibold text-green-300">Class B</span>
                        <span class="rounded-xl bg-[#0d2f6b] px-4 py-1.5 text-sm font-semibold text-blue-300">Class A</span>
                    </div>
                </div>
            </div>

            <div class="mt-5 rounded-2xl border border-zinc-700/70 bg-[#0b0c12]/80 p-4 md:p-5">
                @php
                    $seriesCards = $seriesCards ?? $calendarSeries ?? [];
                    if (empty($seriesCards)) {
                        $seriesCards = collect($upcoming ?? [])
                            ->groupBy('series_name')
                            ->map(function ($group) {
                                $first = $group->first();
                                $tracks = $group->pluck('track_name')->filter()->values();
                                $weeks = [];
                                for ($i = 1; $i <= 12; $i++) {
                                    $weeks[] = [
                                        'week' => $i,
                                        'track' => $tracks[$i - 1] ?? 'Por definir',
                                        'config' => null,
                                    ];
                                }
                                return [
                                    'name' => $first->series_name ?? 'Serie',
                                    'logo' => $first->series_logo ?? null,
                                    'tag' => 'Fixed',
                                    'category' => 'Sports Car',
                                    'start_times' => null,
                                    'race_length' => '12m',
                                    'weeks' => $weeks,
                                ];
                            })
                            ->values()
                            ->all();
                    }
                @endphp

                <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                    <h2 class="text-xl font-bold text-[#e8000d]">Series del calendario</h2>
                    <span class="rounded-full border border-zinc-600/70 bg-zinc-900/60 px-3 py-1 text-[11px] uppercase tracking-[0.2em] text-zinc-400">
                        {{ count($seriesCards) }} series
                    </span>
                </div>

                <div class="grid gap-4 lg:grid-cols-2">
                    @forelse($seriesCards as $series)
                        @php
                            $seriesName = (string) data_get($series, 'name', 'Serie');
                            $seriesLogo = data_get($series, 'logo');
                            $seriesTag = (string) data_get($series, 'tag', 'Fixed');
                            $seriesCategory = (string) data_get($series, 'category', 'Sports Car');
                            $seriesStartTimes = (string) data_get($series, 'start_times', '');
                            $seriesLength = (string) data_get($series, 'race_length', '12m');
                            $weeks = data_get($series, 'weeks', []);
                            $weeks = is_array($weeks) ? array_values($weeks) : [];
                            if (count($weeks) < 12) {
                                for ($i = count($weeks); $i < 12; $i++) {
                                    $weeks[] = [
                                        'week' => $i + 1,
                                        'track' => 'Por definir',
                                        'config' => null,
                                    ];
                                }
                            }
                            $initials = collect(explode(' ', $seriesName))
                                ->filter()
                                ->map(fn ($word) => strtoupper(substr($word, 0, 1)))
                                ->take(2)
                                ->implode('');
                        @endphp
                        <article class="calendar-card rounded-2xl border border-zinc-700/70 bg-[#101010]/85 p-4 shadow-[0_18px_40px_rgba(0,0,0,0.45)]">
                            <div class="flex items-center gap-3">
                                <div class="flex h-14 w-14 items-center justify-center rounded-xl border border-zinc-700/70 bg-black/70">
                                    @if(!empty($seriesLogo))
                                        <img src="{{ $seriesLogo }}" alt="{{ $seriesName }}" class="h-10 w-10 object-contain" loading="lazy">
                                    @else
                                        <span class="text-sm font-bold text-zinc-200">{{ $initials ?: 'SE' }}</span>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h3 class="truncate text-lg font-bold text-white">{{ $seriesName }}</h3>
                                    <p class="text-xs text-zinc-400">{{ $seriesCategory }}</p>
                                </div>
                                <span class="rounded-lg border border-[#7a0007]/80 bg-[#2a0002]/70 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-[#ff4b4b]">
                                    {{ $seriesTag }}
                                </span>
                            </div>

                            <div class="mt-3 grid grid-cols-2 gap-2 text-[11px] text-zinc-300 md:grid-cols-4">
                                <div class="rounded-lg border border-zinc-700/60 bg-black/40 px-2 py-1.5 text-center">
                                    {{ $seriesStartTimes !== '' ? $seriesStartTimes : 'Slots flex' }}
                                </div>
                                <div class="rounded-lg border border-zinc-700/60 bg-black/40 px-2 py-1.5 text-center">
                                    Setups fixed
                                </div>
                                <div class="rounded-lg border border-zinc-700/60 bg-black/40 px-2 py-1.5 text-center">
                                    MPR active
                                </div>
                                <div class="rounded-lg border border-zinc-700/60 bg-black/40 px-2 py-1.5 text-center">
                                    Car + track
                                </div>
                            </div>

                            <div class="mt-3 rounded-xl border border-zinc-700/70 bg-[#0c0d12]/80 p-2">
                                <div class="grid gap-1">
                                    @foreach($weeks as $index => $week)
                                        @php
                                            $weekNumber = (int) data_get($week, 'week', $index + 1);
                                            $weekTrack = (string) data_get($week, 'track', 'Por definir');
                                            $weekConfig = (string) data_get($week, 'config', '');
                                        @endphp
                                        <div class="calendar-week-row rounded-lg border border-zinc-800/60 bg-black/40 px-2 py-1.5 text-sm">
                                            <span class="text-xs font-semibold text-zinc-400">W{{ str_pad((string) $weekNumber, 2, '0', STR_PAD_LEFT) }}</span>
                                            <span class="truncate text-zinc-100">{{ $weekTrack }}</span>
                                            <span class="text-[11px] text-zinc-400">{{ $weekConfig !== '' ? $weekConfig : 'TBD' }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mt-3 flex items-center justify-between text-xs text-zinc-400">
                                <span>{{ $seriesStartTimes !== '' ? 'Races ' . $seriesStartTimes : 'Races every 30m' }}</span>
                                <span class="font-semibold text-zinc-200">{{ $seriesLength }}</span>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-xl border border-zinc-700/60 bg-[#101010]/85 p-4 text-zinc-300">
                            No hay series cacheadas.
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
@endsection

