<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard | Enosis eSports Stats</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black min-h-screen relative text-white">
    <div class="absolute inset-0">
        <img src="{{ asset('img/silverstone1.png') }}" alt="Background" class="w-full h-full object-cover filter blur-sm brightness-75">
    </div>

    <main class="relative z-10 w-full max-w-5xl mx-auto p-4 md:p-6 space-y-4">
        <section class="bg-black/50 backdrop-blur-sm p-4 md:p-6 border border-gray-800/30 rounded-xl shadow-lg">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-bold">Manager Dashboard</h1>
                    <p class="text-gray-300">Team overview and quick navigation</p>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-red-600 rounded hover:bg-red-700">Logout</button>
                </form>
            </div>
            <div class="mt-4 flex flex-wrap gap-2">
                <a href="{{ route('manager.dashboard') }}" class="px-3 py-2 bg-blue-700 rounded hover:bg-blue-800">Dashboard</a>
                <a href="{{ route('manager.leaderboard') }}" class="px-3 py-2 bg-blue-700 rounded hover:bg-blue-800">Leaderboard</a>
                <a href="{{ route('manager.calendar') }}" class="px-3 py-2 bg-blue-700 rounded hover:bg-blue-800">Calendar</a>
            </div>
        </section>

        <section class="bg-black/50 backdrop-blur-sm p-4 md:p-6 border border-gray-800/30 rounded-xl shadow-lg">
            <h2 class="text-xl font-semibold mb-2">Team Snapshot</h2>
            <p class="text-gray-300">Registered drivers: <span class="font-semibold text-white">{{ $driverCount }}</span></p>
        </section>
    </main>
</body>
</html>
