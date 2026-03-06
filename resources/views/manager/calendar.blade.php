@extends('layouts.app')

@section('title', 'Calendario manager | Enosis eSports')
@section('mainClass', 'w-full max-w-5xl mx-auto p-4 md:p-6 space-y-4')

@section('content')
    <section class="bg-black/50 backdrop-blur-sm p-4 md:p-6 border border-gray-800/30 rounded-xl shadow-lg">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <h1 class="text-2xl font-bold">Calendario de carreras</h1>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('manager.dashboard') }}" class="px-3 py-2 bg-blue-700 rounded hover:bg-blue-800">Panel</a>
                <a href="{{ route('manager.leaderboard') }}" class="px-3 py-2 bg-blue-700 rounded hover:bg-blue-800">Clasificacion</a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-3 py-2 bg-red-600 rounded hover:bg-red-700">Cerrar sesion</button>
                </form>
            </div>
        </div>
    </section>

    <section class="bg-black/50 backdrop-blur-sm p-4 md:p-6 border border-gray-800/30 rounded-xl shadow-lg">
        <div class="space-y-2">
            @forelse($upcoming as $race)
                <article class="bg-gray-900/80 rounded px-3 py-2">
                    <p class="font-semibold">{{ $race->series_name ?? 'Serie' }}</p>
                    <p class="text-sm text-gray-300">{{ $race->track_name ?? 'Circuito' }} | {{ optional($race->race_date)->format('d/m/Y H:i') }}</p>
                </article>
            @empty
                <p class="text-gray-300">Todavia no hay proximas carreras cacheadas.</p>
            @endforelse
        </div>
    </section>
@endsection
