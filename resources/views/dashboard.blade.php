@extends('layouts.app')

@section('title', 'Panel | Enosis eSports')
@section('mainClass', 'w-full max-w-5xl mx-auto p-4 md:p-6 space-y-4')

@section('content')
    @php
        $currentUser = $user ?? auth()->user();
        $role = $dashboardRole ?? $currentUser?->role;
    @endphp

    <section class="bg-black/55 border border-gray-800 rounded-xl p-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-3xl font-bold">Bienvenido, {{ $currentUser->name }}</h1>
                <p class="text-gray-200 mt-1">Rol: <span class="uppercase font-semibold">{{ $role }}</span></p>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 bg-red-600 rounded hover:bg-red-700">Cerrar sesion</button>
            </form>
        </div>
    </section>

    @if(session('status'))
        <section class="bg-green-700/80 border border-green-500 rounded p-3">{{ session('status') }}</section>
    @endif

    @if($errors->any())
        <section class="bg-red-700/80 border border-red-500 rounded p-3">{{ $errors->first() }}</section>
    @endif

    @if($role === 'driver')
        <section class="bg-black/55 border border-gray-800 rounded-xl p-5">
            <h2 class="text-xl font-semibold mb-3">Estado de vinculacion iRacing</h2>
            @if($currentUser->iracing_linked)
                <p class="text-green-300 mb-3">Cuenta vinculada correctamente.</p>
                <p class="text-gray-200">Customer ID: {{ $currentUser->iracing_customer_id }}</p>
            @else
                <p class="text-yellow-300 mb-3">
                    Todavia no has vinculado iRacing. Puedes ver contenido general, pero no tus estadisticas personales.
                </p>
                <a href="{{ route('iracing.oauth.redirect') }}" class="inline-block px-4 py-2 bg-blue-600 rounded hover:bg-blue-700">
                    Vincular cuenta de iRacing
                </a>
            @endif
        </section>

        @if($currentUser->iracing_linked)
            <section class="bg-black/55 border border-gray-800 rounded-xl p-5">
                <h2 class="text-xl font-semibold mb-3">Tus estadisticas</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div class="bg-gray-900/80 rounded p-3"><p class="text-sm text-gray-300">iRating</p><p class="text-lg font-bold">{{ $stats->irating ?? '-' }}</p></div>
                    <div class="bg-gray-900/80 rounded p-3"><p class="text-sm text-gray-300">Safety</p><p class="text-lg font-bold">{{ $stats->safety_rating ?? '-' }}</p></div>
                    <div class="bg-gray-900/80 rounded p-3"><p class="text-sm text-gray-300">Victorias</p><p class="text-lg font-bold">{{ $stats->wins ?? 0 }}</p></div>
                    <div class="bg-gray-900/80 rounded p-3"><p class="text-sm text-gray-300">Podios</p><p class="text-lg font-bold">{{ $stats->podiums ?? 0 }}</p></div>
                </div>
            </section>
        @endif

        <section class="bg-black/55 border border-gray-800 rounded-xl p-5">
            <h2 class="text-xl font-semibold mb-3">Contenido general</h2>
            <p class="text-gray-200">Calendario iRacing, coches y series (contenido compartido para todos los pilotos).</p>
        </section>
    @endif

    @if($role === 'manager')
        <section class="bg-black/55 border border-gray-800 rounded-xl p-5">
            <h2 class="text-xl font-semibold mb-3">Panel manager</h2>
            <p class="text-gray-200 mb-3">Pilotos registrados: {{ $driverCount ?? '-' }}</p>
            <div class="flex gap-2">
                <a class="px-3 py-2 bg-blue-700 rounded hover:bg-blue-800" href="{{ route('manager.leaderboard') }}">Clasificacion</a>
                <a class="px-3 py-2 bg-blue-700 rounded hover:bg-blue-800" href="{{ route('manager.calendar') }}">Calendario</a>
            </div>
        </section>

        @if(($section ?? null) === 'leaderboard')
            <section class="bg-black/55 border border-gray-800 rounded-xl p-5">
                <h3 class="text-lg font-semibold mb-3">Clasificacion de pilotos</h3>
                <div class="space-y-2">
                    @forelse(($drivers ?? collect()) as $driver)
                        <div class="flex justify-between bg-gray-900/80 rounded px-3 py-2">
                            <span>{{ $driver->name }}</span>
                            <span>{{ $driver->driverStats->irating ?? '-' }}</span>
                        </div>
                    @empty
                        <p class="text-gray-300">Sin datos todavia.</p>
                    @endforelse
                </div>
            </section>
        @endif

        @if(($section ?? null) === 'calendar')
            <section class="bg-black/55 border border-gray-800 rounded-xl p-5">
                <h3 class="text-lg font-semibold mb-3">Proximas carreras</h3>
                <div class="space-y-2">
                    @forelse(($upcoming ?? collect()) as $race)
                        <div class="bg-gray-900/80 rounded px-3 py-2">
                            <p class="font-semibold">{{ $race->series_name ?? 'Serie' }}</p>
                            <p class="text-sm text-gray-300">{{ $race->track_name ?? 'Circuito' }} - {{ optional($race->race_date)->format('d/m/Y H:i') }}</p>
                        </div>
                    @empty
                        <p class="text-gray-300">Sin calendario cacheado.</p>
                    @endforelse
                </div>
            </section>
        @endif
    @endif

    @if($role === 'admin')
        <section class="bg-black/55 border border-gray-800 rounded-xl p-5">
            <h2 class="text-xl font-semibold mb-3">Panel administrador</h2>
            <p class="text-gray-200">Managers: {{ ($managers ?? collect())->count() }} | Pilotos: {{ ($drivers ?? collect())->count() }}</p>
        </section>

        <section class="bg-black/55 border border-gray-800 rounded-xl p-5">
            <h3 class="text-lg font-semibold mb-3">Crear manager</h3>
            <form action="{{ route('admin.managers.create') }}" method="POST" class="grid md:grid-cols-3 gap-3">
                @csrf
                <input type="text" name="name" placeholder="Nombre" class="px-3 py-2 rounded bg-white/10 border border-gray-700">
                <input type="email" name="email" placeholder="Correo" class="px-3 py-2 rounded bg-white/10 border border-gray-700">
                <input type="password" name="password" placeholder="Contrasena" class="px-3 py-2 rounded bg-white/10 border border-gray-700">
                <button type="submit" class="px-4 py-2 bg-blue-700 rounded hover:bg-blue-800 md:col-span-3">Crear manager</button>
            </form>
        </section>
    @endif
@endsection
