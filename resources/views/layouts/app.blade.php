<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Enosis eSports Stats')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="/favicon.ico">
</head>
<body class="@yield('bodyClass', 'bg-black min-h-screen relative text-white')">
    <div class="absolute inset-0">
        <img src="{{ asset('img/silverstone1.png') }}" alt="Background" class="w-full h-full object-cover filter blur-sm brightness-75">
    </div>

    <div class="relative z-10 min-h-screen flex flex-col">
        @if(trim($__env->yieldContent('hideHeader')) === '')
            @include('layouts.partials.header')
        @endif

        <main class="@yield('mainClass', 'w-full max-w-6xl mx-auto p-4 md:p-6 lg:p-8')">
            @yield('content')
        </main>
    </div>
</body>
</html>
