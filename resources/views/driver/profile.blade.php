<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Dashboard | Enosis eSports Stats</title>
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
                    <h1 class="text-2xl font-bold">Driver Dashboard</h1>
                    <p class="text-gray-300">Welcome, {{ $user->name }}</p>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-red-600 rounded hover:bg-red-700">Logout</button>
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
            <h2 class="text-xl font-semibold mb-3">iRacing Link Status</h2>
            @if($user->iracing_linked)
                <p class="text-green-300">Linked. Customer ID: {{ $user->iracing_customer_id }}</p>
            @else
                <p class="text-yellow-300 mb-3">Your iRacing account is not linked yet. You can only access general content for now.</p>
                <a href="{{ route('iracing.oauth.redirect') }}" class="inline-block px-4 py-2 bg-blue-600 rounded hover:bg-blue-700">
                    Link iRacing Account
                </a>
            @endif
        </section>

        @if($user->iracing_linked)
            <section class="bg-black/50 backdrop-blur-sm p-4 md:p-6 border border-gray-800/30 rounded-xl shadow-lg">
                <h2 class="text-xl font-semibold mb-3">Personal Stats</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div class="bg-gray-900/80 rounded p-3"><p class="text-xs text-gray-300">iRating</p><p class="text-lg font-bold">{{ $stats->irating ?? '-' }}</p></div>
                    <div class="bg-gray-900/80 rounded p-3"><p class="text-xs text-gray-300">Safety</p><p class="text-lg font-bold">{{ $stats->safety_rating ?? '-' }}</p></div>
                    <div class="bg-gray-900/80 rounded p-3"><p class="text-xs text-gray-300">Wins</p><p class="text-lg font-bold">{{ $stats->wins ?? 0 }}</p></div>
                    <div class="bg-gray-900/80 rounded p-3"><p class="text-xs text-gray-300">Podiums</p><p class="text-lg font-bold">{{ $stats->podiums ?? 0 }}</p></div>
                </div>
            </section>

            <section class="bg-black/50 backdrop-blur-sm p-4 md:p-6 border border-gray-800/30 rounded-xl shadow-lg">
                <h2 class="text-xl font-semibold mb-3">Recent Results</h2>
                <div class="space-y-2">
                    @forelse($results as $race)
                        <article class="bg-gray-900/80 rounded px-3 py-2">
                            <p class="font-semibold">{{ $race->series_name ?? 'Series' }} - {{ $race->track_name ?? 'Track' }}</p>
                            <p class="text-sm text-gray-300">P{{ $race->finish_position ?? '-' }} | Inc {{ $race->incidents ?? '-' }} | {{ optional($race->race_date)->format('d/m/Y H:i') }}</p>
                        </article>
                    @empty
                        <p class="text-gray-300">No cached races yet.</p>
                    @endforelse
                </div>
            </section>
        @else
            <section class="bg-black/50 backdrop-blur-sm p-4 md:p-6 border border-gray-800/30 rounded-xl shadow-lg">
                <h2 class="text-xl font-semibold mb-2">General Content</h2>
                <p class="text-gray-300">Without iRacing link you can browse shared pages such as calendar, cars and series information.</p>
            </section>
        @endif
    </main>
</body>
</html>
