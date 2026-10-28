<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use App\Notifications\ConfirmerNouvelEmail;
use App\Notifications\EmailModifie;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $nouvelEmail = $request->validated('email');
        $user->fill(collect($request->validated())->except('email')->all());

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = $request->file('avatar')->store('avatars', 'public');
        }

        $user->save();

        // Changement d'adresse : la nouvelle n'est appliquee qu'apres confirmation du lien envoye a cette adresse.
        if ($nouvelEmail !== mb_strtolower($user->email)) {
            if ($erreur = $this->demanderChangementEmail($user, $nouvelEmail)) {
                return Redirect::route('profile.edit')->withInput()->withErrors(['email' => $erreur]);
            }

            return Redirect::route('profile.edit')->with('status', 'email-change-sent');
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function renvoyerConfirmationEmail(Request $request): RedirectResponse
    {
        $user = $request->user();
        if ($user->pending_email && ($erreur = $this->demanderChangementEmail($user, $user->pending_email))) {
            return Redirect::route('profile.edit')->withErrors(['email' => $erreur]);
        }

        return Redirect::route('profile.edit')->with('status', 'email-change-sent');
    }

    public function annulerChangementEmail(Request $request): RedirectResponse
    {
        $request->user()->forceFill(['pending_email' => null])->save();

        return Redirect::route('profile.edit')->with('status', 'email-change-cancelled');
    }

    /** Lien signe recu sur la nouvelle adresse : valable seulement pour la derniere demande en cours. */
    public function confirmerEmail(Request $request, User $user): RedirectResponse
    {
        $email = mb_strtolower((string) $request->query('email'));
        $destination = Auth::check() ? route('profile.edit') : route('login');

        if (!$user->pending_email || $email !== $user->pending_email) {
            return redirect($destination)->withErrors(['email' => 'Ce lien de confirmation n\'est plus valable. Refaites la demande depuis votre profil.']);
        }
        if (User::where('email', $email)->whereKeyNot($user->id)->exists()) {
            $user->forceFill(['pending_email' => null])->save();

            return redirect($destination)->withErrors(['email' => User::MESSAGE_EMAIL_INDISPONIBLE]);
        }

        $ancien = $user->email;
        $user->forceFill(['email' => $email, 'pending_email' => null, 'email_verified_at' => now()])->save();

        try {
            Notification::route('mail', $ancien)->notify(new EmailModifie($email));
        } catch (\Throwable $e) {
            report($e);
        }

        // Le profil affiche un message selon le code ; la page de connexion affiche le texte tel quel.
        return redirect($destination)->with('status', Auth::check() ? 'email-changed' : 'Votre nouvelle adresse e-mail est confirmée. Connectez-vous avec celle-ci.');
    }

    /** @return string|null message d'erreur a afficher, null si le lien a bien ete envoye */
    private function demanderChangementEmail(User $user, string $nouvelEmail): ?string
    {
        // Au plus 3 demandes par 10 minutes : evite d'utiliser le formulaire pour inonder une boite mail.
        $cle = 'email-change:' . $user->id;
        if (RateLimiter::tooManyAttempts($cle, 3)) {
            return 'Trop de demandes de changement d\'adresse. Réessayez dans quelques minutes.';
        }
        RateLimiter::hit($cle, 600);

        if (User::where('email', $nouvelEmail)->whereKeyNot($user->id)->exists()) {
            return User::MESSAGE_EMAIL_INDISPONIBLE;
        }

        $user->forceFill(['pending_email' => $nouvelEmail])->save();

        try {
            Notification::route('mail', $nouvelEmail)->notify(new ConfirmerNouvelEmail($user, $nouvelEmail));
        } catch (\Throwable $e) {
            report($e);

            return 'Envoi de l\'e-mail de confirmation impossible pour le moment. Réessayez plus tard.';
        }

        return null;
    }

    public function removeAvatar(Request $request): RedirectResponse
    {
        $user = $request->user();
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $user->avatar = null;
            $user->save();
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
