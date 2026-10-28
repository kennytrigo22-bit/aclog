@extends('layouts.apprenant')

@section('title', 'Mon Profil')
@section('page-title', 'Mon Profil')

@section('content')
<div class="max-w-3xl space-y-6">

    {{-- Photo de profil --}}
    <div class="bg-white dark:bg-surface-dark rounded-2xl p-6 shadow-sm">
        <h2 class="font-heading text-lg font-bold text-primary-700 dark:text-white mb-1">Photo de profil</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">Ajoutez une photo pour personnaliser votre profil.</p>

        <div class="flex items-center gap-5">
            <div class="w-20 h-20 rounded-full bg-primary-700 flex items-center justify-center text-white text-2xl font-bold shrink-0 overflow-hidden">
                @if($user->avatar)
                    <img src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar" class="w-full h-full object-cover">
                @else
                    {{ strtoupper(substr($user->prenom ?? $user->name, 0, 1)) }}{{ strtoupper(substr($user->name, 0, 1)) }}
                @endif
            </div>
            <div class="flex-1">
                <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="flex items-center gap-3">
                    @csrf
                    @method('patch')
                    <input type="hidden" name="name" value="{{ $user->name }}">
                    <input type="hidden" name="email" value="{{ $user->email }}">
                    <label class="cursor-pointer px-4 py-2 rounded-lg bg-primary-700 text-white text-sm font-bold hover:bg-primary-500 transition">
                        Choisir une photo
                        <input type="file" name="avatar" accept="image/*" class="hidden" onchange="this.form.submit()">
                    </label>
                    @error('avatar')<p class="text-red-500 text-xs">{{ $message }}</p>@enderror
                </form>
                @if($user->avatar)
                <form method="post" action="{{ route('profile.avatar.remove') }}" class="mt-2">
                    @csrf
                    @method('delete')
                    <button type="submit" class="text-xs text-red-500 hover:underline">Supprimer la photo</button>
                </form>
                @endif
                <p class="text-xs text-gray-400 mt-1">JPG, PNG ou GIF. Max 2 Mo.</p>
            </div>
        </div>
    </div>

    {{-- Informations personnelles --}}
    <div class="bg-white dark:bg-surface-dark rounded-2xl p-6 shadow-sm">
        <h2 class="font-heading text-lg font-bold text-primary-700 dark:text-white mb-1">Informations personnelles</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">Mettez à jour votre nom et adresse email.</p>

        <form id="send-verification" method="post" action="{{ route('verification.send') }}">@csrf</form>
        <form id="email-resend" method="post" action="{{ route('profile.email.resend') }}">@csrf</form>
        <form id="email-cancel" method="post" action="{{ route('profile.email.cancel') }}">@csrf @method('DELETE')</form>

        <form method="post" action="{{ route('profile.update') }}" class="space-y-4">
            @csrf
            @method('patch')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Nom</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="prenom" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Prénom</label>
                    <input id="prenom" name="prenom" type="text" value="{{ old('prenom', $user->prenom ?? '') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                    @error('prenom')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label for="email" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Adresse email</label>
                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                <p class="text-xs text-gray-400 mt-1">Pour changer d'adresse, saisissez la nouvelle : un lien de confirmation lui sera envoyé. Votre adresse actuelle reste active jusqu'à la confirmation.</p>

                @if (session('status') === 'email-changed')
                <p class="mt-2 text-sm text-emerald-600 font-medium">Votre nouvelle adresse e-mail est confirmée.</p>
                @elseif (session('status') === 'email-change-cancelled')
                <p class="mt-2 text-sm text-gray-500 font-medium">Le changement d'adresse a été annulé.</p>
                @endif

                @if ($user->pending_email)
                <div class="mt-3 p-3 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 text-sm text-amber-800 dark:text-amber-300">
                    @if (session('status') === 'email-change-sent')<p class="font-semibold mb-1">Lien de confirmation envoyé.</p>@endif
                    <p>En attente de confirmation : <strong class="break-all">{{ $user->pending_email }}</strong>. Cliquez sur le lien reçu à cette adresse (valable 60 minutes).</p>
                    <div class="flex flex-wrap gap-3 mt-2">
                        <button form="email-resend" class="underline font-semibold">Renvoyer le lien</button>
                        <button form="email-cancel" class="underline font-semibold">Annuler le changement</button>
                    </div>
                </div>
                @endif

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2">
                    <p class="text-sm text-amber-600 dark:text-amber-400">
                        Votre adresse email n'est pas vérifiée.
                        <button form="send-verification" class="underline font-semibold hover:text-amber-800 dark:hover:text-amber-300">Renvoyer le lien de vérification</button>
                    </p>
                    @if (session('status') === 'verification-link-sent')
                    <p class="mt-1 text-sm text-emerald-600 font-medium">Un nouveau lien de vérification a été envoyé.</p>
                    @endif
                </div>
                @endif
            </div>

            <div>
                <label for="telephone" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Téléphone</label>
                <input id="telephone" name="telephone" type="tel" value="{{ old('telephone', $user->telephone ?? '') }}" placeholder="+242 06 000 00 00" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                @error('telephone')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            @include('partials.profil-fields', ['user' => $user])

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-primary-700 text-white font-bold text-sm hover:bg-primary-500 transition shadow">Enregistrer</button>
                @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)" class="text-sm text-emerald-600 font-medium">Profil mis à jour !</p>
                @endif
            </div>
        </form>
    </div>

    {{-- Changer le mot de passe --}}
    <div class="bg-white dark:bg-surface-dark rounded-2xl p-6 shadow-sm">
        <h2 class="font-heading text-lg font-bold text-primary-700 dark:text-white mb-1">Changer le mot de passe</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">Utilisez un mot de passe long et aléatoire pour sécuriser votre compte.</p>

        <form method="post" action="{{ route('password.update') }}" class="space-y-4">
            @csrf
            @method('put')

            <div>
                <label for="current_password" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Mot de passe actuel</label>
                <input id="current_password" name="current_password" type="password" autocomplete="current-password" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                @error('current_password', 'updatePassword')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Nouveau mot de passe</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                    @error('password', 'updatePassword')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Confirmer</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-primary-700 text-white font-bold text-sm hover:bg-primary-500 transition shadow">Modifier le mot de passe</button>
                @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)" class="text-sm text-emerald-600 font-medium">Mot de passe mis à jour !</p>
                @endif
            </div>
        </form>
    </div>

    {{-- Mes récompenses --}}
    <div class="bg-white dark:bg-surface-dark rounded-2xl p-6 shadow-sm">
        <h2 class="font-heading text-lg font-bold text-primary-700 dark:text-white mb-4">Mes Récompenses</h2>
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-accent-500/10 rounded-xl p-4 text-center">
                <p class="text-3xl font-bold text-accent-500">{{ $user->points }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Points gagnés</p>
            </div>
            <div class="bg-emerald-500/10 rounded-xl p-4 text-center">
                <p class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($user->jetons) }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Jetons FCFA</p>
            </div>
        </div>
        <p class="text-xs text-gray-500 mt-3">100 points = 5 000 FCFA. Les jetons sont applicables comme réduction sur vos prochaines formations.</p>
    </div>

    {{-- Supprimer le compte --}}
    <div class="bg-white dark:bg-surface-dark rounded-2xl p-6 shadow-sm border border-red-200 dark:border-red-900/30" x-data="{ confirmDelete: false }">
        <h2 class="font-heading text-lg font-bold text-red-600 dark:text-red-400 mb-1">Supprimer mon compte</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Cette action est irréversible. Toutes vos données seront définitivement supprimées.</p>

        <button @click="confirmDelete = true" x-show="!confirmDelete" class="px-4 py-2 rounded-lg bg-red-600 text-white text-sm font-bold hover:bg-red-700 transition">Supprimer mon compte</button>

        <form method="post" action="{{ route('profile.destroy') }}" x-show="confirmDelete" x-transition class="space-y-4 mt-4 p-4 bg-red-50 dark:bg-red-900/10 rounded-xl">
            @csrf
            @method('delete')
            <p class="text-sm text-red-700 dark:text-red-300 font-medium">Confirmez en entrant votre mot de passe :</p>
            <input name="password" type="password" placeholder="Votre mot de passe" required class="w-full rounded-lg border-red-300 dark:border-red-700 dark:bg-surface-darker text-sm focus:ring-red-500 focus:border-red-500">
            @error('password', 'userDeletion')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            <div class="flex gap-3">
                <button type="submit" class="px-4 py-2 rounded-lg bg-red-600 text-white text-sm font-bold hover:bg-red-700 transition">Confirmer la suppression</button>
                <button type="button" @click="confirmDelete = false" class="px-4 py-2 rounded-lg bg-gray-200 dark:bg-gray-700 text-sm font-bold hover:bg-gray-300 dark:hover:bg-gray-600 transition">Annuler</button>
            </div>
        </form>
    </div>

</div>
@endsection
