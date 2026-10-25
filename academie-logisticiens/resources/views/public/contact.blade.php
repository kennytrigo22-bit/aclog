@extends('layouts.public')

@section('title', 'Contact - ' . $siteTitre)

@section('content')
<div class="pt-28"></div>

<section class="bg-primary-700 text-white py-12 lg:py-16 relative overflow-hidden">
    <div class="absolute inset-0">
        <img src="{{ asset('assets/images/thumbs/businessman-touching-virtual-screen.jpg') }}" alt="" class="w-full h-full object-cover opacity-15">
    </div>
    <div class="max-w-7xl mx-auto px-4 lg:px-6 relative z-10">
        <h1 class="font-heading text-3xl md:text-5xl font-bold">Contactez-nous</h1>
        <p class="text-lg text-gray-300 mt-4 max-w-2xl">Une question sur nos formations ? Besoin d'un renseignement ? Notre équipe à Brazzaville vous répond rapidement.</p>
    </div>
</section>

<section class="w-full py-10 lg:py-16 bg-surface-light dark:bg-surface-darker">
    <div class="max-w-7xl mx-auto px-4 lg:px-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Formulaire --}}
            <div class="lg:col-span-2">
                @if(session('success'))
                <div class="mb-6 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-sm">
                    {{ session('success') }}
                </div>
                @endif
                @if(session('error'))
                <div class="mb-6 p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 text-sm">
                    {{ session('error') }}
                </div>
                @endif

                <form method="POST" action="{{ route('contact.envoyer') }}" class="bg-white dark:bg-surface-dark rounded-2xl p-6 lg:p-8 shadow-sm space-y-5">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="nom" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Nom complet</label>
                            <input type="text" name="nom" id="nom" value="{{ old('nom') }}" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker focus:ring-accent-500 focus:border-accent-500" placeholder="Votre nom">
                            @error('nom')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Adresse email</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker focus:ring-accent-500 focus:border-accent-500" placeholder="votre@email.com">
                            @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div>
                        <label for="objet" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Objet</label>
                        <input type="text" name="objet" id="objet" value="{{ old('objet') }}" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker focus:ring-accent-500 focus:border-accent-500" placeholder="Sujet de votre message">
                        @error('objet')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="message" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Message</label>
                        <textarea name="message" id="message" rows="5" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker focus:ring-accent-500 focus:border-accent-500" placeholder="Décrivez votre demande...">{{ old('message') }}</textarea>
                        @error('message')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="px-8 py-3 rounded-lg bg-accent-500 text-primary-700 font-bold hover:bg-accent-400 transition-all shadow">
                        Envoyer le message
                    </button>
                </form>
            </div>

            {{-- Infos contact --}}
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white dark:bg-surface-dark rounded-2xl p-6 shadow-sm">
                    <h3 class="font-heading font-bold text-primary-700 dark:text-white mb-4">Nos coordonnées</h3>
                    <ul class="space-y-4 text-sm">
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-accent-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <div>
                                <p class="font-semibold text-gray-700 dark:text-gray-300">Adresse</p>
                                <p class="text-gray-500">4 Rue Abila, Talangai<br>Brazzaville, République du Congo</p>
                            </div>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-accent-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <div>
                                <p class="font-semibold text-gray-700 dark:text-gray-300">Téléphone</p>
                                <p class="text-gray-500">+242 06 828 79 15</p>
                            </div>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-accent-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <div>
                                <p class="font-semibold text-gray-700 dark:text-gray-300">Email</p>
                                <p class="text-gray-500">{{ $emailContact }}</p>
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="bg-primary-700 rounded-2xl p-6 text-white">
                    <h3 class="font-heading font-bold mb-3">Horaires d'ouverture</h3>
                    <ul class="space-y-2 text-sm text-gray-300">
                        <li class="flex justify-between"><span>Lundi - Vendredi</span><span class="font-semibold text-white">8h - 18h</span></li>
                        <li class="flex justify-between"><span>Samedi</span><span class="font-semibold text-white">9h - 14h</span></li>
                        <li class="flex justify-between"><span>Dimanche</span><span class="text-gray-400">Fermé</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
