<footer class="bg-primary-700 text-white">
    <div class="max-w-7xl mx-auto px-4 lg:px-6 py-12 md:py-16">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-12">
            {{-- Logo & Description --}}
            <div class="lg:col-span-1">
                <div class="flex items-center gap-3 mb-4">
                    <img src="{{ asset('assets/logo/logo-192.jpg') }}" alt="Logo" class="w-[100px] h-[100px] rounded-lg object-contain bg-white p-1">
                    <div>
                        <span class="font-heading font-bold text-lg block leading-tight">{{ $siteTitre }}</span>
                        <span class="text-xs italic text-accent-400">{{ $siteSlogan }}</span>
                    </div>
                </div>
                <p class="text-sm text-gray-300 leading-relaxed">
                    Le 1er cabinet académique digital de référence en Afrique Centrale. Formations certifiantes en logistique, transport, commerce international et douane.
                </p>
            </div>

            {{-- Liens rapides --}}
            <div>
                <h4 class="font-heading font-bold text-accent-400 mb-4">Liens Rapides</h4>
                <ul class="space-y-2 text-sm text-gray-300">
                    <li><a href="{{ route('home') }}" class="hover:text-white transition">Accueil</a></li>
                    <li><a href="{{ route('formations.index') }}" class="hover:text-white transition">Formations</a></li>
                    <li><a href="{{ route('tests.index') }}" class="hover:text-white transition">Tests de Niveau</a></li>
                    <li><a href="{{ route('a-propos') }}" class="hover:text-white transition">À Propos</a></li>
                    <li><a href="{{ route('contact') }}" class="hover:text-white transition">Contact</a></li>
                </ul>
            </div>

            {{-- Domaines --}}
            <div>
                <h4 class="font-heading font-bold text-accent-400 mb-4">Nos Domaines</h4>
                <ul class="space-y-2 text-sm text-gray-300">
                    <li>Logistique & Supply Chain</li>
                    <li>Commerce International</li>
                    <li>Douane & Transit CEMAC</li>
                    <li>Transport & Distribution</li>
                </ul>
            </div>

            {{-- Contact --}}
            <div>
                <h4 class="font-heading font-bold text-accent-400 mb-4">Contact</h4>
                <ul class="space-y-3 text-sm text-gray-300">
                    <li class="flex items-start gap-2">
                        <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>4 Rue Abila, Talangai<br>Brazzaville, Congo</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        <span>+242 06 828 79 15</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span>{{ $emailContact }}</span>
                    </li>
                </ul>
            </div>
        </div>

        @if(collect($identifiantsLegaux)->filter()->isNotEmpty())
        <p class="mt-10 pt-6 border-t border-primary-600 text-center text-xs text-gray-300 tracking-wide">
            @foreach(collect($identifiantsLegaux)->filter() as $libelle => $valeur)
                <span class="whitespace-nowrap"><strong class="text-accent-400">{{ $libelle }}</strong> : {{ $valeur }}</span>@if(!$loop->last)<span class="mx-2 text-primary-500">|</span>@endif
            @endforeach
        </p>
        @endif

        <div class="border-t border-primary-600 mt-6 pt-6 flex flex-col md:flex-row items-center justify-between gap-4 text-xs text-gray-400">
            <span>&copy; {{ date('Y') }} {{ $siteTitre }}. Tous droits réservés.</span>
            <div class="flex gap-4">
                <a href="{{ route('cgv') }}" class="hover:text-white transition">CGV</a>
                <a href="{{ route('confidentialite') }}" class="hover:text-white transition">Confidentialité</a>
                <a href="{{ route('mentions-legales') }}" class="hover:text-white transition">Mentions légales</a>
            </div>
        </div>
    </div>
</footer>
