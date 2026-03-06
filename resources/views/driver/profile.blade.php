@extends('layouts.app')

@section('title', 'Panel piloto | Enosis eSports')
@section('mainClass', 'w-full max-w-5xl mx-auto p-4 md:p-6 space-y-4')

@section('content')
    <section class="bg-black/50 backdrop-blur-sm p-4 md:p-6 border border-gray-800/30 rounded-xl shadow-lg">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold">Panel piloto</h1>
                <p class="text-gray-300">Bienvenido, {{ $user->name }}</p>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-red-600 rounded hover:bg-red-700">Cerrar sesion</button>
            </form>
        </div>
    </section>

    @if(session('status'))
        <section class="bg-green-700/80 border border-green-500 rounded p-3">{{ session('status') }}</section>
    @endif

    @if($errors->any())
        <section class="bg-red-700/80 border border-red-500 rounded p-3">{{ $errors->first() }}</section>
    @endif

    <section class="bg-black/50 backdrop-blur-sm p-4 md:p-6 border border-gray-800/30 rounded-xl shadow-lg">
        <h2 class="text-xl font-semibold mb-3">Estado de vinculacion iRacing</h2>
        @if($user->iracing_linked)
            <p class="text-green-300">Cuenta vinculada. Customer ID: {{ $user->iracing_customer_id }}</p>
        @else
            <p class="text-yellow-300 mb-3">Tu cuenta de iRacing aun no esta vinculada. Por ahora solo puedes acceder a contenido general.</p>
            <a href="{{ route('iracing.oauth.redirect') }}" class="inline-block px-4 py-2 bg-blue-600 rounded hover:bg-blue-700">
                Vincular cuenta iRacing
            </a>
        @endif
    </section>

    @if($user->iracing_linked)
        <section class="bg-black/50 backdrop-blur-sm p-4 md:p-6 border border-gray-800/30 rounded-xl shadow-lg">
            <h2 class="text-xl font-semibold mb-3">Estadisticas personales</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <div class="bg-gray-900/80 rounded p-3"><p class="text-xs text-gray-300">iRating</p><p class="text-lg font-bold">{{ $stats->irating ?? '-' }}</p></div>
                <div class="bg-gray-900/80 rounded p-3"><p class="text-xs text-gray-300">Safety rating</p><p class="text-lg font-bold">{{ $stats->safety_rating ?? '-' }}</p></div>
                <div class="bg-gray-900/80 rounded p-3"><p class="text-xs text-gray-300">Victorias</p><p class="text-lg font-bold">{{ $stats->wins ?? 0 }}</p></div>
                <div class="bg-gray-900/80 rounded p-3"><p class="text-xs text-gray-300">Podios</p><p class="text-lg font-bold">{{ $stats->podiums ?? 0 }}</p></div>
            </div>
        </section>

        <section class="bg-black/50 backdrop-blur-sm p-4 md:p-6 border border-gray-800/30 rounded-xl shadow-lg">
            <h2 class="text-xl font-semibold mb-3">Resultados recientes</h2>
            <div class="space-y-2">
                @forelse($results as $race)
                    <article class="bg-gray-900/80 rounded px-3 py-2">
                        <p class="font-semibold">{{ $race->series_name ?? 'Serie' }} - {{ $race->track_name ?? 'Circuito' }}</p>
                        <p class="text-sm text-gray-300">P{{ $race->finish_position ?? '-' }} | Inc {{ $race->incidents ?? '-' }} | {{ optional($race->race_date)->format('d/m/Y H:i') }}</p>
                    </article>
                @empty
                    <p class="text-gray-300">Todavia no hay carreras cacheadas.</p>
                @endforelse
            </div>
        </section>
    @else
        <section class="bg-black/50 backdrop-blur-sm p-4 md:p-6 border border-gray-800/30 rounded-xl shadow-lg">
            <h2 class="text-xl font-semibold mb-2">Contenido general</h2>
            <p class="text-gray-300">Sin vincular iRacing puedes consultar paginas compartidas como calendario, coches y series.</p>
        </section>
    @endif
@endsection
