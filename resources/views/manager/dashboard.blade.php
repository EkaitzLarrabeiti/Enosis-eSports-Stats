@extends('layouts.app')

@section('title', 'Panel manager | Enosis eSports')
@section('mainClass', 'w-full max-w-5xl mx-auto p-4 md:p-6 space-y-4')

@section('content')
    <section class="bg-black/50 backdrop-blur-sm p-4 md:p-6 border border-gray-800/30 rounded-xl shadow-lg">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold">Panel manager</h1>
                <p class="text-gray-300">Resumen del equipo y navegacion rapida</p>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-red-600 rounded hover:bg-red-700">Cerrar sesion</button>
            </form>
        </div>
        <div class="mt-4 flex flex-wrap gap-2">
            <a href="{{ route('manager.dashboard') }}" class="px-3 py-2 bg-blue-700 rounded hover:bg-blue-800">Panel</a>
            <a href="{{ route('manager.leaderboard') }}" class="px-3 py-2 bg-blue-700 rounded hover:bg-blue-800">Clasificacion</a>
            <a href="{{ route('manager.calendar') }}" class="px-3 py-2 bg-blue-700 rounded hover:bg-blue-800">Calendario</a>
        </div>
    </section>

    <section class="bg-black/50 backdrop-blur-sm p-4 md:p-6 border border-gray-800/30 rounded-xl shadow-lg">
        <h2 class="text-xl font-semibold mb-2">Resumen rapido</h2>
        <p class="text-gray-300">Pilotos registrados: <span class="font-semibold text-white">{{ $driverCount }}</span></p>
    </section>
@endsection
