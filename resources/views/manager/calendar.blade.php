<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Calendar | Enosis eSports Stats</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black min-h-screen relative text-white">
    <div class="absolute inset-0">
        <img src="{{ asset('img/silverstone1.png') }}" alt="Background" class="w-full h-full object-cover filter blur-sm brightness-75">
    </div>

    <main class="relative z-10 max-w-5xl mx-auto p-4 md:p-6 space-y-4">
        <section class="bg-black/50 backdrop-blur-sm p-4 md:p-6 border border-gray-800/30 rounded-xl shadow-lg">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <h1 class="text-2xl font-bold">Race Calendar</h1>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('manager.dashboard') }}" class="px-3 py-2 bg-blue-700 rounded hover:bg-blue-800">Dashboard</a>
                    <a href="{{ route('manager.leaderboard') }}" class="px-3 py-2 bg-blue-700 rounded hover:bg-blue-800">Leaderboard</a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="px-3 py-2 bg-red-600 rounded hover:bg-red-700">Logout</button>
                    </form>
                </div>
            </div>
        </section>

        <section class="bg-black/50 backdrop-blur-sm p-4 md:p-6 border border-gray-800/30 rounded-xl shadow-lg">
            <div class="space-y-2">
                @forelse($upcoming as $race)
                    <article class="bg-gray-900/80 rounded px-3 py-2">
                        <p class="font-semibold">{{ $race->series_name ?? 'Series' }}</p>
                        <p class="text-sm text-gray-300">{{ $race->track_name ?? 'Track' }} | {{ optional($race->race_date)->format('d/m/Y H:i') }}</p>
                    </article>
                @empty
                    <p class="text-gray-300">No upcoming races cached yet.</p>
                @endforelse
            </div>
        </section>
    </main>
</body>
</html>
