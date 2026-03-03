<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Enosis eSports Stats</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black h-screen w-screen relative">
    <!-- Fondo difuminado -->
    <div class="absolute inset-0">
        <img src="{{ asset('img/silverstone1.png') }}"
             alt="Background"
             class="w-full h-full object-cover filter blur-sm brightness-75">
    </div>
    <div class="relative z-10 max-w-4xl mx-auto p-6">
        <h1 class="text-3xl font-bold mb-4 text-white">Welcome, {{ Auth::user()->name }}</h1>
        <p class="mb-4 text-gray-100">Your iRacing account is
            @if(Auth::user()->iracingAccount)
                linked as {{ Auth::user()->iracingAccount->display_name }}.
            @else
                not linked yet.
            @endif
        </p>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">Logout</button>
        </form>
        @if(!Auth::user()->iracingAccount)
            <a href="{{ route('iracing.link') }}"
            class="inline-block mt-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                Link iRacing Account
            </a>
        @endif
    </div>
</body>
</html>
