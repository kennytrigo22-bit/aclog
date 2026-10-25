@extends('layouts.public')
@section('title', 'Annuaire des Certifies - ' . $siteTitre)

@section('content')
<div class="pt-28"></div>

<section class="bg-primary-700 text-white py-10 lg:py-14 relative overflow-hidden">
    <div class="absolute inset-0">
        <img src="{{ asset('assets/images/thumbs/global-logistics-transportation-network.jpg') }}" alt="" class="w-full h-full object-cover opacity-15">
    </div>
    <div class="max-w-4xl mx-auto px-4 lg:px-6 relative z-10">
        <span class="text-xs font-bold text-accent-400 uppercase tracking-wider">Annuaire des Certifies</span>
        <h1 class="font-heading text-3xl md:text-4xl font-bold mt-2">Nos etudiants certifies</h1>
        <p class="text-gray-300 mt-2 max-w-2xl">Consultez la liste des professionnels certifies par le Cabinet ACLOG ou verifiez l'authenticite d'un certificat.</p>
    </div>
</section>

{{-- Verification de certificat --}}
<section class="py-8 bg-white dark:bg-surface-dark shadow-sm">
    <div class="max-w-3xl mx-auto px-4 lg:px-6">
        <div class="bg-surface-light dark:bg-surface-darker rounded-2xl p-6 lg:p-8">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-accent-400/20 text-accent-500 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div>
                    <h2 class="font-heading font-bold text-primary-700 dark:text-white">Verifier un certificat</h2>
                    <p class="text-sm text-gray-500">Entrez le code de verification ou scannez le QR code du certificat</p>
                </div>
            </div>
            <form action="{{ route('annuaire-certifies') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
                <input type="text" name="code" value="{{ request('code') }}" placeholder="Ex: AC-BZV-XXXX" class="flex-1 px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-surface-dark text-gray-900 dark:text-white focus:ring-2 focus:ring-accent-500 focus:border-accent-500 font-mono">
                <button type="submit" class="px-6 py-3 rounded-lg bg-accent-500 text-primary-700 font-bold hover:bg-accent-400 transition shadow-sm flex items-center gap-2 justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    Verifier
                </button>
            </form>

            @if(request('code') && isset($certificatRecherche))
                <div class="mt-6">
                    @if($certificatRecherche)
                        <div class="bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 rounded-xl p-4 flex items-start gap-3">
                            <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-emerald-700 dark:text-emerald-400">Certificat authentique</p>
                                <div class="mt-2 text-sm space-y-1 text-gray-700 dark:text-gray-300">
                                    <p><span class="text-gray-500">Titulaire :</span> <strong>{{ $certificatRecherche->user->nom_complet ?? $certificatRecherche->user->name }}</strong></p>
                                    <p><span class="text-gray-500">Formation :</span> <strong>{{ $certificatRecherche->formation->titre }}</strong></p>
                                    <p><span class="text-gray-500">Score :</span> <strong>{{ $certificatRecherche->score_obtenu }}%</strong></p>
                                    <p><span class="text-gray-500">Delivre le :</span> <strong>{{ $certificatRecherche->issued_at->format('d/m/Y') }}</strong></p>
                                    <p><span class="text-gray-500">Code :</span> <span class="font-mono font-bold">{{ $certificatRecherche->code_verification }}</span></p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl p-4 flex items-start gap-3">
                            <div class="w-8 h-8 rounded-full bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-400 flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </div>
                            <div>
                                <p class="font-semibold text-red-700 dark:text-red-400">Certificat non trouve</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Le code <span class="font-mono font-bold">{{ request('code') }}</span> ne correspond a aucun certificat.</p>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</section>

{{-- Statistiques --}}
<section class="py-6 bg-surface-light dark:bg-surface-darker">
    <div class="max-w-6xl mx-auto px-4 lg:px-6">
        <div class="grid grid-cols-3 gap-6">
            <div class="text-center">
                <span class="font-heading text-3xl font-bold text-primary-700 dark:text-white">{{ $totalCertifies }}</span>
                <p class="text-sm text-gray-500 font-medium mt-1">etudiants certifies</p>
            </div>
            <div class="text-center">
                <span class="font-heading text-3xl font-bold text-primary-700 dark:text-white">{{ $totalFormations }}</span>
                <p class="text-sm text-gray-500 font-medium mt-1">formations suivies</p>
            </div>
            <div class="text-center">
                <span class="font-heading text-3xl font-bold text-primary-700 dark:text-white">{{ $totalPublics }}</span>
                <p class="text-sm text-gray-500 font-medium mt-1">certificats publics</p>
            </div>
        </div>
    </div>
</section>

{{-- Liste des certifies publics --}}
<section class="py-10 lg:py-16 bg-white dark:bg-surface-dark">
    <div class="max-w-6xl mx-auto px-4 lg:px-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
            <div>
                <h2 class="font-heading text-xl md:text-2xl font-bold text-primary-700 dark:text-white">Certifies publics</h2>
                <p class="text-sm text-gray-500 mt-1">Etudiants ayant accepte de rendre leur certificat visible</p>
            </div>
            <form action="{{ route('annuaire-certifies') }}" method="GET" class="flex gap-2">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Rechercher un nom..." class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-surface-darker text-sm">
                <button type="submit" class="px-4 py-2 rounded-lg bg-primary-700 text-white text-sm font-medium hover:bg-primary-600 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </button>
            </form>
        </div>

        @if($certifiesPublics->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="text-left py-3 px-4 font-semibold text-gray-500 uppercase tracking-wider text-xs">Nom</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-500 uppercase tracking-wider text-xs">Formation</th>
                        <th class="text-center py-3 px-4 font-semibold text-gray-500 uppercase tracking-wider text-xs">Score</th>
                        <th class="text-center py-3 px-4 font-semibold text-gray-500 uppercase tracking-wider text-xs">Date</th>
                        <th class="text-center py-3 px-4 font-semibold text-gray-500 uppercase tracking-wider text-xs">Verifier</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($certifiesPublics as $cert)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition">
                        <td class="py-3 px-4">
                            <span class="font-semibold text-primary-700 dark:text-white">{{ $cert->user->nom_complet ?? $cert->user->name }}</span>
                        </td>
                        <td class="py-3 px-4 text-gray-600 dark:text-gray-400">{{ $cert->formation->titre }}</td>
                        <td class="py-3 px-4 text-center">
                            <span class="px-2 py-1 rounded-full bg-emerald-100 dark:bg-emerald-900 text-emerald-700 dark:text-emerald-300 text-xs font-bold">{{ $cert->score_obtenu }}%</span>
                        </td>
                        <td class="py-3 px-4 text-center text-gray-500">{{ $cert->issued_at->format('d/m/Y') }}</td>
                        <td class="py-3 px-4 text-center">
                            <a href="{{ route('certificat.verifier', $cert->code_verification) }}" class="text-accent-500 hover:text-accent-600 font-semibold text-xs">
                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-6">
            {{ $certifiesPublics->links() }}
        </div>
        @else
        <div class="text-center py-12 bg-surface-light dark:bg-surface-darker rounded-2xl">
            <div class="w-16 h-16 rounded-full bg-primary-50 dark:bg-gray-800 text-primary-700 dark:text-accent-400 mx-auto flex items-center justify-center mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
            </div>
            <h3 class="font-heading font-bold text-primary-700 dark:text-white">Aucun certificat public pour le moment</h3>
            <p class="text-sm text-gray-500 mt-2 max-w-md mx-auto">Les etudiants certifies apparaitront ici lorsqu'ils choisiront de rendre leur certificat visible.</p>
        </div>
        @endif
    </div>
</section>
@endsection
