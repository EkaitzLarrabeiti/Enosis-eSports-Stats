<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Enosis eSports Stats</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black flex items-center justify-center h-screen">
    <!-- Fondo difuminado -->
    <div class="absolute inset-0">
        <img src="{{ asset('img/silverstone1.png') }}"
             alt="Background"
             class="w-full h-full object-cover filter blur-sm brightness-75">
    </div>
    <div class="bg-black/50 backdrop-blur-sm p-8 border border-gray-800/30 rounded-xl shadow-lg w-full max-w-md relative z-10">
        <h1 class="text-2xl font-bold mb-6 text-center text-white">Enosis eSports Stats</h1>

        @if($errors->any())
            <div class="bg-red-100 text-red-700 p-2 mb-4 rounded">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="email" class="block mb-1 font-medium text-white">Email</label>
                <input type="email" name="email" id="email" required class="w-full px-3 bg-white/25 py-2 border rounded focus:outline-none focus:ring focus:border-blue-300">
            </div>
            <div>
                <label for="password" class="block mb-1 font-medium text-white">Contraseña</label>
                <input type="password" name="password" id="password" required class="w-full px-3 bg-white/25 py-2 border rounded focus:outline-none focus:ring focus:border-blue-300">
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition">Login</button>
        </form>

        <p class="mt-4 text-center text-gray-400">
            Primera vez? <a href="{{ route('register') }}" class="text-blue-600 hover:underline">Crea tu cuenta</a>
        </p>
    </div>
</body>
</html>
