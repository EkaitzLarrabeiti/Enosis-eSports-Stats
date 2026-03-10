<footer class="w-full border-t border-gray-800/70 bg-black/75 backdrop-blur-sm mt-auto">
    <div class="w-full px-16 md:px-20 sm:px-8 py-6 flex flex-wrap items-center justify-between">
        <div class="footer-brand inline-flex items-center gap-2">
            <a href="{{ url('/') }}" class="inline-flex items-center" aria-label="Enosis eSports">
                <img src="{{ asset('img/Enosis-WhiteRed.png') }}" alt="Enosis" class="h-6 md:h-5 w-auto">
                <span class="pl-2 text-3xl md:text-2xl font-black tracking-[0.12em] text-red-500 leading-none" style="font-family: 'Orbitron', monospace;">ESPORTS</span>
            </a>
        </div>

        <div class="footer-meta text-[11px] tracking-[0.14em] text-gray-400 uppercase">
            &copy; {{ date('Y') }} ENOSIS ESPORTS - ALL RIGHTS RESERVED
        </div>

        <div class="footer-socials flex items-center gap-4">
            <a href="https://x.com/e_enosis"
               class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-gray-700/50 bg-white/5 transition hover:bg-white/10"
               target="_blank"
               rel="noopener noreferrer"
               aria-label="X">
                <img src="{{ asset('img/socials/twitter.png') }}" alt="X" class="h-8 w-8 object-contain opacity-90">
            </a>
            <a href="https://www.tiktok.com/@enosisesports"
               class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-gray-700/50 bg-white/5 transition hover:bg-white/10"
               target="_blank"
               rel="noopener noreferrer"
               aria-label="TikTok">
                <img src="{{ asset('img/socials/tiktok.png') }}" alt="TikTok" class="h-8 w-8 object-contain opacity-90">
            </a>
            <a href="https://www.instagram.com/enosisesports/"
               class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-gray-700/50 bg-white/5 transition hover:bg-white/10"
               target="_blank"
               rel="noopener noreferrer"
               aria-label="Instagram">
                <img src="{{ asset('img/socials/instagram.png') }}" alt="Instagram" class="h-8 w-8 object-contain opacity-90">
            </a>
        </div>
    </div>
</footer>
