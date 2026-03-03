<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Enosis eSports Stats</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black flex items-center justify-center h-screen">
    <div class="bg-gray-900 p-8 rounded-2xl shadow-md w-full max-w-md">
        <h1 class="text-2xl font-bold mb-6 text-center text-white">Enosis eSports Stats</h1>

        @if($errors->any())
            <div class="bg-red-100 text-red-700 p-2 mb-4 rounded">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('register') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="name" class="block mb-1 font-medium text-white">Nombre</label>
                <input type="name" name="name" id="name" required class="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:border-blue-300">
            </div>
            <div>
                <label for="nickname" class="block mb-1 font-medium text-white">Nickname</label>
                <input type="nickname" name="nickname" id="nickname" required class="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:border-blue-300">
            </div>
            <div>
                <label for="email" class="block mb-1 font-medium text-white">Email</label>
                <input type="email" name="email" id="email" required class="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:border-blue-300">
            </div>
            <div>
                <label for="password" class="block mb-1 font-medium text-white">Contraseña</label>
                <input type="password" name="password" id="password" required class="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:border-blue-300">
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition">Crear</button>
        </form>

        <p class="mt-4 text-center text-gray-500">
            Ya tienes una cuenta? <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Entrar</a>
        </p>
    </div>
</body>
</html>
