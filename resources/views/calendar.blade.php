@extends('layouts.app')

@section('title', 'Calendario | Enosis eSports Stats')
@section('mainClass', 'w-full max-w-5xl mx-auto p-4 md:p-6 space-y-4')

@section('content')
    <section class="bg-black/50 backdrop-blur-sm p-4 md:p-6 border border-gray-800/30 rounded-xl shadow-lg">
        <h1 class="text-2xl md:text-3xl font-bold mb-2">Calendario de carreras</h1>
        <p class="text-gray-300">
            Proximas carreras cacheadas por la plataforma.
        </p>
    </section>

    <section class="bg-black/50 backdrop-blur-sm p-4 md:p-6 border border-gray-800/30 rounded-xl shadow-lg">
        <div class="space-y-2">
            @forelse($upcoming as $race)
                <article class="bg-gray-900/80 rounded px-3 py-2">
                    <p class="font-semibold">{{ $race->series_name ?? 'Serie' }}</p>
                    <p class="text-sm text-gray-300">{{ $race->track_name ?? 'Circuito' }} | {{ optional($race->race_date)->format('d/m/Y H:i') }}</p>
                </article>
            @empty
                <p class="text-gray-300">No hay carreras proximas cacheadas.</p>
            @endforelse
        </div>
    </section>
@endsection
