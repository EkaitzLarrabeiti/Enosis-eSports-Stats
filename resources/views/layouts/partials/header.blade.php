<header class="w-full border-b border-gray-800/50 bg-black/60 backdrop-blur-sm">
    <div class="max-w-6xl mx-auto px-4 md:px-6 py-3 flex items-center justify-between gap-3">
        <div class="inline-flex items-center gap-2">
            <a href="{{ url('/') }}" class="inline-flex items-center" aria-label="Enosis eSports">
                <img src="{{ asset('img/Enosis-WhiteRed.png') }}" alt="Enosis" class="h-6 md:h-5 w-auto">
            <span class="pl-2 text-3xl md:text-2xl font-black tracking-[0.12em] text-red-500 leading-none" style="font-family: 'Orbitron', monospace;">ESPORTS</span>
            </a>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('calendar') }}" class="px-3 py-2 rounded-lg border border-blue-500/60 bg-blue-600/80 hover:bg-blue-700 text-sm font-medium">
                Calendario
            </a>

            @auth
                <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-lg border border-gray-600 bg-white/10 hover:bg-white/20 text-sm font-medium">
                    Estadísticas
                </a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-3 py-2 rounded-lg border border-red-500/70 bg-red-600/80 hover:bg-red-700 text-sm font-medium">
                        Cerrar sesion
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="px-3 py-2 rounded-lg border border-gray-600 bg-white/10 hover:bg-white/20 text-sm font-medium">
                    Iniciar sesion / Registro
                </a>
            @endauth
        </div>
    </div>
</header>
