@extends('layouts.app')

@section('title', 'Inicio | Enosis eSports')
@section('mainClass', 'w-full max-w-6xl mx-auto p-4 md:p-6 lg:p-8')

@section('content')
    <section class="bg-black/50 backdrop-blur-sm border border-gray-800/30 rounded-2xl shadow-lg p-6 md:p-10">
        <div class="flex flex-col lg:flex-row gap-8 lg:items-center">
            <div class="flex-1">
                <p class="text-blue-300 text-sm uppercase tracking-wider mb-2">Presentacion del equipo</p>
                <h1 class="text-3xl md:text-5xl font-bold leading-tight mb-4">Enosis eSports Racing Stats</h1>
                <p class="text-gray-200 text-base md:text-lg mb-6">
                    Plataforma privada para pilotos, managers y administradores de Enosis eSports.
                    Sigue el rendimiento del equipo, la evolucion de la clasificacion y las proximas pruebas de iRacing.
                </p>
                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('login') }}" class="px-5 py-3 bg-blue-600 rounded-lg font-semibold text-center hover:bg-blue-700">Iniciar sesion</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="px-5 py-3 bg-white/10 border border-gray-600 rounded-lg text-center hover:bg-white/20">Crear cuenta de piloto</a>
                    @endif
                </div>
            </div>
            <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-3">
                <article class="bg-gray-900/75 rounded-xl p-4 border border-gray-800">
                    <h2 class="font-semibold mb-1">Zona de piloto</h2>
                    <p class="text-sm text-gray-300">Panel personal y vinculacion de iRacing desde el perfil.</p>
                </article>
                <article class="bg-gray-900/75 rounded-xl p-4 border border-gray-800">
                    <h2 class="font-semibold mb-1">Zona de manager</h2>
                    <p class="text-sm text-gray-300">Leaderboard del equipo, calendario de carreras y rendimiento global.</p>
                </article>
                <article class="bg-gray-900/75 rounded-xl p-4 border border-gray-800 sm:col-span-2">
                    <h2 class="font-semibold mb-1">Zona de administracion</h2>
                    <p class="text-sm text-gray-300">Control de usuarios y roles para operar la plataforma.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="mt-4 md:mt-6 grid grid-cols-1 md:grid-cols-3 gap-3">
        <div class="bg-black/50 backdrop-blur-sm border border-gray-800/30 rounded-xl p-4">
            <h3 class="font-semibold mb-1">Integracion OAuth2</h3>
            <p class="text-sm text-gray-300">Vinculacion de cuenta iRacing con almacenamiento seguro de tokens.</p>
        </div>
        <div class="bg-black/50 backdrop-blur-sm border border-gray-800/30 rounded-xl p-4">
            <h3 class="font-semibold mb-1">Cache de estadisticas</h3>
            <p class="text-sm text-gray-300">Estadisticas de pilotos e historial de carreras cacheados en base de datos.</p>
        </div>
        <div class="bg-black/50 backdrop-blur-sm border border-gray-800/30 rounded-xl p-4">
            <h3 class="font-semibold mb-1">Diseno responsive</h3>
            <p class="text-sm text-gray-300">Optimizado para escritorio y navegacion movil.</p>
        </div>
    </section>
@endsection
