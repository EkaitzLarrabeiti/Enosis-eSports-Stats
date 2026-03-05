<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Enosis eSports Stats</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black min-h-screen w-screen relative text-white">
    <div class="absolute inset-0">
        <img src="{{ asset('img/silverstone1.png') }}"
             alt="Background"
             class="w-full h-full object-cover filter blur-sm brightness-75">
    </div>
    <div class="relative z-10 max-w-5xl mx-auto p-6">
        @php
            $currentUser = $user ?? auth()->user();
            $role = $dashboardRole ?? $currentUser?->role;
        @endphp

        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold">Bienvenido, {{ $currentUser->name }}</h1>
                <p class="text-gray-200 mt-1">Rol: <span class="uppercase font-semibold">{{ $role }}</span></p>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 bg-red-600 rounded hover:bg-red-700">Cerrar sesión</button>
            </form>
        </div>

        @if(session('status'))
            <div class="bg-green-700/80 border border-green-500 rounded p-3 mb-4">{{ session('status') }}</div>
        @endif

        @if($errors->any())
            <div class="bg-red-700/80 border border-red-500 rounded p-3 mb-4">{{ $errors->first() }}</div>
        @endif

        @if($role === 'driver')
            <div class="bg-black/55 border border-gray-800 rounded-xl p-5 mb-4">
                <h2 class="text-xl font-semibold mb-3">Estado de vinculación iRacing</h2>
                @if($currentUser->iracing_linked)
                    <p class="text-green-300 mb-3">Cuenta vinculada correctamente.</p>
                    <p class="text-gray-200">Customer ID: {{ $currentUser->iracing_customer_id }}</p>
                @else
                    <p class="text-yellow-300 mb-3">
                        Todavía no has vinculado iRacing. Puedes ver contenido general, pero no tus estadísticas personales.
                    </p>
                    <a href="{{ route('iracing.oauth.redirect') }}"
                       class="inline-block px-4 py-2 bg-blue-600 rounded hover:bg-blue-700">
                        Vincular cuenta de iRacing
                    </a>
                @endif
            </div>

            @if($currentUser->iracing_linked)
                <div class="bg-black/55 border border-gray-800 rounded-xl p-5 mb-4">
                    <h2 class="text-xl font-semibold mb-3">Tus estadísticas</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div class="bg-gray-900/80 rounded p-3"><p class="text-sm text-gray-300">iRating</p><p class="text-lg font-bold">{{ $stats->irating ?? '-' }}</p></div>
                        <div class="bg-gray-900/80 rounded p-3"><p class="text-sm text-gray-300">Safety</p><p class="text-lg font-bold">{{ $stats->safety_rating ?? '-' }}</p></div>
                        <div class="bg-gray-900/80 rounded p-3"><p class="text-sm text-gray-300">Victorias</p><p class="text-lg font-bold">{{ $stats->wins ?? 0 }}</p></div>
                        <div class="bg-gray-900/80 rounded p-3"><p class="text-sm text-gray-300">Podios</p><p class="text-lg font-bold">{{ $stats->podiums ?? 0 }}</p></div>
                    </div>
                </div>
            @endif

            <div class="bg-black/55 border border-gray-800 rounded-xl p-5">
                <h2 class="text-xl font-semibold mb-3">Contenido general</h2>
                <p class="text-gray-200">Calendario iRacing, coches y series (contenido compartido para todos los drivers).</p>
            </div>
        @endif

        @if($role === 'manager')
            <div class="bg-black/55 border border-gray-800 rounded-xl p-5 mb-4">
                <h2 class="text-xl font-semibold mb-3">Panel manager</h2>
                <p class="text-gray-200 mb-3">Drivers registrados: {{ $driverCount ?? '-' }}</p>
                <div class="flex gap-2">
                    <a class="px-3 py-2 bg-blue-700 rounded hover:bg-blue-800" href="{{ route('manager.leaderboard') }}">Leaderboard</a>
                    <a class="px-3 py-2 bg-blue-700 rounded hover:bg-blue-800" href="{{ route('manager.calendar') }}">Calendario</a>
                </div>
            </div>

            @if(($section ?? null) === 'leaderboard')
                <div class="bg-black/55 border border-gray-800 rounded-xl p-5 mb-4">
                    <h3 class="text-lg font-semibold mb-3">Leaderboard de drivers</h3>
                    <div class="space-y-2">
                        @forelse(($drivers ?? collect()) as $driver)
                            <div class="flex justify-between bg-gray-900/80 rounded px-3 py-2">
                                <span>{{ $driver->name }}</span>
                                <span>{{ $driver->driverStats->irating ?? '-' }}</span>
                            </div>
                        @empty
                            <p class="text-gray-300">Sin datos todavía.</p>
                        @endforelse
                    </div>
                </div>
            @endif

            @if(($section ?? null) === 'calendar')
                <div class="bg-black/55 border border-gray-800 rounded-xl p-5 mb-4">
                    <h3 class="text-lg font-semibold mb-3">Próximas carreras</h3>
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
                </div>
            @endif
        @endif

        @if($role === 'admin')
            <div class="bg-black/55 border border-gray-800 rounded-xl p-5 mb-4">
                <h2 class="text-xl font-semibold mb-3">Panel administrador</h2>
                <p class="text-gray-200">Managers: {{ ($managers ?? collect())->count() }} | Drivers: {{ ($drivers ?? collect())->count() }}</p>
            </div>

            <div class="bg-black/55 border border-gray-800 rounded-xl p-5">
                <h3 class="text-lg font-semibold mb-3">Crear manager</h3>
                <form action="{{ route('admin.managers.create') }}" method="POST" class="grid md:grid-cols-3 gap-3">
                    @csrf
                    <input type="text" name="name" placeholder="Nombre" class="px-3 py-2 rounded bg-white/10 border border-gray-700">
                    <input type="email" name="email" placeholder="Email" class="px-3 py-2 rounded bg-white/10 border border-gray-700">
                    <input type="password" name="password" placeholder="Password" class="px-3 py-2 rounded bg-white/10 border border-gray-700">
                    <button type="submit" class="px-4 py-2 bg-blue-700 rounded hover:bg-blue-800 md:col-span-3">Crear manager</button>
                </form>
            </div>
        @endif
    </div>
</body>
</html>
