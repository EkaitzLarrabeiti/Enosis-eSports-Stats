<header class="w-full border-b border-gray-800/70 bg-black/50 backdrop-blur-sm">
    <div class="w-full px-16 md:px-20 py-3 flex items-center justify-between">
        <div class="inline-flex items-center">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-3" aria-label="Enosis eSports">
                <img src="{{ asset('img/Enosis-WhiteRed.png') }}" alt="Enosis" class="h-6 md:h-5 w-auto">
                <span class="hidden sm:inline-block text-3xl md:text-2xl font-black tracking-[0.12em] text-red-600 leading-none" style="font-family: 'Orbitron', monospace;">ESPORTS</span>
            </a>
        </div>

        <div class="hidden sm:flex items-center gap-2">
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

        <div class="sm:hidden relative">
            <button id="mobileMenuButton" type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-700/70 bg-white/5 text-white" aria-expanded="false" aria-controls="mobileMenu">
                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <path d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
                <span class="sr-only">Menu</span>
            </button>
            <div id="mobileMenu" class="absolute right-0 mt-2 w-56 rounded-lg border border-gray-700/70 bg-black/95 p-2 shadow-lg hidden">
                <a href="{{ route('calendar') }}" class="block rounded-md px-3 py-2 text-sm text-white hover:bg-white/10">
                    Calendario
                </a>
                @auth
                    <a href="{{ route('dashboard') }}" class="mt-1 block rounded-md px-3 py-2 text-sm text-white hover:bg-white/10">
                        Estadísticas
                    </a>
                    <form action="{{ route('logout') }}" method="POST" class="mt-1">
                        @csrf
                        <button type="submit" class="w-full rounded-md px-3 py-2 text-left text-sm text-white hover:bg-white/10">
                            Cerrar sesion
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="mt-1 block rounded-md px-3 py-2 text-sm text-white hover:bg-white/10">
                        Iniciar sesion / Registro
                    </a>
                @endauth
            </div>
        </div>
    </div>
</header>
<script>
    (() => {
        const button = document.getElementById('mobileMenuButton');
        const menu = document.getElementById('mobileMenu');
        if (!button || !menu) return;
        button.addEventListener('click', () => {
            menu.classList.toggle('hidden');
            const expanded = button.getAttribute('aria-expanded') === 'true';
            button.setAttribute('aria-expanded', String(!expanded));
        });
        document.addEventListener('click', (event) => {
            if (!menu.classList.contains('hidden') && !menu.contains(event.target) && !button.contains(event.target)) {
                menu.classList.add('hidden');
                button.setAttribute('aria-expanded', 'false');
            }
        });
    })();
</script>
