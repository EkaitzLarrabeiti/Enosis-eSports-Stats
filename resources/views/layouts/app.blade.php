<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Enosis eSports Stats')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Barlow+Condensed:wght@300;400;600;700;900&family=Orbitron:wght@400;700;900&display=swap" rel="stylesheet">
    @stack('head')
</head>
<body class="@yield('bodyClass', 'bg-black min-h-screen relative text-white')">
    <div class="absolute inset-0">
        <img src="{{ asset('img/silverstone1.png') }}" alt="Background" class="w-full h-full object-cover filter blur-sm brightness-60">
    </div>

    <div class="relative z-10 min-h-screen flex flex-col">
        @if(trim($__env->yieldContent('hideHeader')) === '')
            @include('layouts.partials.header')
        @endif

        <main class="@yield('mainClass', 'w-full max-w-6xl mx-auto p-4 md:p-6 lg:p-8')">
            @yield('content')
        </main>

        @if(trim($__env->yieldContent('hideFooter')) === '')
            @include('layouts.partials.footer')
        @endif
    </div>
</body>
</html>
