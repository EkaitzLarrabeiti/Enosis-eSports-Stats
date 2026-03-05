<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enosis eSports Racing Stats</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black min-h-screen relative text-white">
    <div class="absolute inset-0">
        <img src="{{ asset('img/silverstone1.png') }}" alt="Background" class="w-full h-full object-cover filter blur-sm brightness-75">
    </div>

    <main class="relative z-10 max-w-6xl mx-auto p-4 md:p-6 lg:p-8">
        <section class="bg-black/50 backdrop-blur-sm border border-gray-800/30 rounded-2xl shadow-lg p-6 md:p-10">
            <div class="flex flex-col lg:flex-row gap-8 lg:items-center">
                <div class="flex-1">
                    <p class="text-blue-300 text-sm uppercase tracking-wider mb-2">Team Presentation</p>
                    <h1 class="text-3xl md:text-5xl font-bold leading-tight mb-4">Enosis eSports Racing Stats</h1>
                    <p class="text-gray-200 text-base md:text-lg mb-6">
                        Private web platform for Enosis eSports drivers, managers and admins.
                        Track performance, review leaderboard evolution, and follow the upcoming iRacing calendar.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <a href="{{ route('login') }}" class="px-5 py-3 bg-blue-600 rounded-lg font-semibold text-center hover:bg-blue-700">Login</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="px-5 py-3 bg-white/10 border border-gray-600 rounded-lg text-center hover:bg-white/20">Create Driver Account</a>
                        @endif
                    </div>
                </div>
                <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <article class="bg-gray-900/75 rounded-xl p-4 border border-gray-800">
                        <h2 class="font-semibold mb-1">Driver Area</h2>
                        <p class="text-sm text-gray-300">Personal dashboard and iRacing linking from profile.</p>
                    </article>
                    <article class="bg-gray-900/75 rounded-xl p-4 border border-gray-800">
                        <h2 class="font-semibold mb-1">Manager Area</h2>
                        <p class="text-sm text-gray-300">Team leaderboard, race calendar and overall performance.</p>
                    </article>
                    <article class="bg-gray-900/75 rounded-xl p-4 border border-gray-800 sm:col-span-2">
                        <h2 class="font-semibold mb-1">Admin Area</h2>
                        <p class="text-sm text-gray-300">User and role control for platform operations.</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="mt-4 md:mt-6 grid grid-cols-1 md:grid-cols-3 gap-3">
            <div class="bg-black/50 backdrop-blur-sm border border-gray-800/30 rounded-xl p-4">
                <h3 class="font-semibold mb-1">OAuth2 Integration</h3>
                <p class="text-sm text-gray-300">iRacing account linking with secure token storage.</p>
            </div>
            <div class="bg-black/50 backdrop-blur-sm border border-gray-800/30 rounded-xl p-4">
                <h3 class="font-semibold mb-1">Stats Cache</h3>
                <p class="text-sm text-gray-300">Driver stats and race history cached in database.</p>
            </div>
            <div class="bg-black/50 backdrop-blur-sm border border-gray-800/30 rounded-xl p-4">
                <h3 class="font-semibold mb-1">Responsive Layout</h3>
                <p class="text-sm text-gray-300">Optimized for desktop and mobile browsing.</p>
            </div>
        </section>
    </main>
</body>
</html>
