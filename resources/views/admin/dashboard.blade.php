<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Enosis eSports Stats</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black min-h-screen relative text-white">
    <div class="absolute inset-0">
        <img src="{{ asset('img/silverstone1.png') }}" alt="Background" class="w-full h-full object-cover filter blur-sm brightness-75">
    </div>

    <main class="relative z-10 max-w-6xl mx-auto p-4 md:p-6 space-y-4">
        <section class="bg-black/50 backdrop-blur-sm p-4 md:p-6 border border-gray-800/30 rounded-xl shadow-lg">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-bold">Admin Dashboard</h1>
                    <p class="text-gray-300">User management panel</p>
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
            <h2 class="text-xl font-semibold mb-3">Create Manager</h2>
            <form action="{{ route('admin.managers.create') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                @csrf
                <input type="text" name="name" placeholder="Name" class="px-3 py-2 rounded bg-white/10 border border-gray-700">
                <input type="email" name="email" placeholder="Email" class="px-3 py-2 rounded bg-white/10 border border-gray-700">
                <input type="password" name="password" placeholder="Password" class="px-3 py-2 rounded bg-white/10 border border-gray-700">
                <button type="submit" class="md:col-span-3 px-4 py-2 bg-blue-700 rounded hover:bg-blue-800">Create manager</button>
            </form>
        </section>

        <section class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-black/50 backdrop-blur-sm p-4 md:p-6 border border-gray-800/30 rounded-xl shadow-lg">
                <h3 class="text-lg font-semibold mb-3">Managers</h3>
                <div class="space-y-2">
                    @forelse($managers as $manager)
                        <p class="bg-gray-900/80 rounded px-3 py-2">{{ $manager->name }} <span class="text-gray-400">({{ $manager->email }})</span></p>
                    @empty
                        <p class="text-gray-300">No managers yet.</p>
                    @endforelse
                </div>
            </div>
            <div class="bg-black/50 backdrop-blur-sm p-4 md:p-6 border border-gray-800/30 rounded-xl shadow-lg">
                <h3 class="text-lg font-semibold mb-3">Drivers</h3>
                <div class="space-y-2">
                    @forelse($drivers as $driver)
                        <p class="bg-gray-900/80 rounded px-3 py-2">{{ $driver->name }} <span class="text-gray-400">({{ $driver->email }})</span></p>
                    @empty
                        <p class="text-gray-300">No drivers yet.</p>
                    @endforelse
                </div>
            </div>
        </section>
    </main>
</body>
</html>
