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
    </style>
@endpush

@section('content')
    <div class="calendar-page space-y-6">
        <section class="rounded-2xl border border-zinc-700/60 bg-[#000000]/70 p-5 md:p-6 shadow-lg">
            <div class="rounded-2xl border border-zinc-800/70 bg-[#0f1015]/80 p-4 shadow-inner">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex flex-wrap gap-2">
                        <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-zinc-700/70 bg-[#14151c]/90 px-4 py-2 text-sm font-semibold text-zinc-200">
                            <span class="text-lg">★</span>
                            Favorites
                        </button>
                        <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-zinc-700/70 bg-[#14151c]/90 px-4 py-2 text-sm font-semibold text-zinc-200">
                            <span class="text-lg">👁</span>
                            Hidden
                        </button>
                    </div>

                    <div class="flex flex-1 flex-col gap-3 lg:flex-row lg:items-center lg:justify-center">
                        <label class="flex w-full max-w-md items-center rounded-xl border border-zinc-700/70 bg-[#14151c]/90 px-4 py-2 text-sm text-zinc-300">
                            <span class="mr-2 text-zinc-500">🔎</span>
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
                        <span class="text-lg">↻</span>
                        Reset
                    </button>
                </div>

                <div class="mt-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex flex-wrap gap-2">
                        <span class="inline-flex items-center gap-2 rounded-xl bg-[#f2b310] px-4 py-1.5 text-sm font-semibold text-black">
                            <span>🏎</span> Sports car
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-xl bg-[#f2b310] px-4 py-1.5 text-sm font-semibold text-black">
                            <span>🏁</span> Formula car
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-xl border border-zinc-700/70 bg-[#14151c]/90 px-4 py-1.5 text-sm font-semibold text-zinc-300">
                            <span>◯</span> Oval
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-xl border border-zinc-700/70 bg-[#14151c]/90 px-4 py-1.5 text-sm font-semibold text-zinc-300">
                            <span>◎</span> Dirt oval
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-xl border border-zinc-700/70 bg-[#14151c]/90 px-4 py-1.5 text-sm font-semibold text-zinc-300">
                            <span>〰</span> Dirt road
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-xl border border-zinc-700/70 bg-[#14151c]/90 px-4 py-1.5 text-sm font-semibold text-zinc-300">
                            <span>🏁</span> Endurance
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
                <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                    <h2 class="text-xl font-bold text-[#e8000d]">Series del calendario</h2>
                    <span class="rounded-full border border-zinc-600/70 bg-zinc-900/60 px-3 py-1 text-[11px] uppercase tracking-[0.2em] text-zinc-400">
                        {{ $upcoming->count() }} eventos
                    </span>
                </div>

                <div class="grid gap-3">
                    @forelse($upcoming as $race)
                        <article class="rounded-xl border border-zinc-700/60 bg-[#101010]/85 p-4">
                            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                <div>
                                    <p class="text-lg font-bold text-white">{{ $race->series_name ?? 'Serie' }}</p>
                                    <p class="text-sm text-zinc-300">{{ $race->track_name ?? 'Circuito' }}</p>
                                </div>
                                <div class="flex items-center gap-3 text-sm text-zinc-300">
                                    <span class="rounded-full border border-[#7a0007]/80 bg-[#0d0d0d]/70 px-3 py-1 text-[11px] uppercase tracking-[0.18em] text-[#e8000d]">
                                        {{ optional($race->race_date)->format('d M Y') ?: '-' }}
                                    </span>
                                    <span class="rounded-full border border-zinc-700/80 bg-zinc-900/60 px-3 py-1 text-[11px] uppercase tracking-[0.18em] text-zinc-300">
                                        {{ optional($race->race_date)->format('H:i') ?: '--:--' }}
                                    </span>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-xl border border-zinc-700/60 bg-[#101010]/85 p-4 text-zinc-300">
                            No hay carreras próximas cacheadas.
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
@endsection
