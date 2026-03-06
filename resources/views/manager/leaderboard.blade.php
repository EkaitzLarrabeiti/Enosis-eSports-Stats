@extends('layouts.app')

@section('title', 'Clasificacion manager | Enosis eSports')
@section('mainClass', 'w-full max-w-5xl mx-auto p-4 md:p-6 space-y-4')

@section('content')
    <section class="bg-black/50 backdrop-blur-sm p-4 md:p-6 border border-gray-800/30 rounded-xl shadow-lg">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <h1 class="text-2xl font-bold">Clasificacion</h1>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('manager.dashboard') }}" class="px-3 py-2 bg-blue-700 rounded hover:bg-blue-800">Panel</a>
                <a href="{{ route('manager.calendar') }}" class="px-3 py-2 bg-blue-700 rounded hover:bg-blue-800">Calendario</a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-3 py-2 bg-red-600 rounded hover:bg-red-700">Cerrar sesion</button>
                </form>
            </div>
        </div>
    </section>

    <section class="bg-black/50 backdrop-blur-sm p-4 md:p-6 border border-gray-800/30 rounded-xl shadow-lg">
        <div class="space-y-2">
            @forelse($drivers as $index => $driver)
                <article class="bg-gray-900/80 rounded px-3 py-2 flex items-center justify-between gap-3">
                    <div>
                        <p class="font-semibold">#{{ $index + 1 }} {{ $driver->name }}</p>
                        <p class="text-xs text-gray-300">{{ $driver->nickname ?? '-' }}</p>
                    </div>
                    <p class="font-bold">{{ $driver->driverStats->irating ?? '-' }}</p>
                </article>
            @empty
                <p class="text-gray-300">Todavia no hay datos en la clasificacion.</p>
            @endforelse
        </div>
    </section>
@endsection
