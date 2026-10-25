<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\BienvenueApprenant;
use App\Support\ProfilOptions;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'telephone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ] + ProfilOptions::regles(), ProfilOptions::messages());

        $user = User::create(array_merge(
            collect($data)->except(['password', 'password_confirmation'])->all(),
            [
                'password' => Hash::make($data['password']),
                'role' => 'apprenant',
                'accept_marketing' => $request->boolean('accept_marketing'),
                'pays' => $data['pays'] ?? 'Congo',
            ]
        ));

        try {
            event(new Registered($user));
        } catch (\Throwable $e) {
            // SMTP indisponible : le compte est cree, l'utilisateur pourra demander un nouvel e-mail de verification.
            report($e);
        }

        try {
            $user->notify(new BienvenueApprenant());
        } catch (\Throwable) {}

        Auth::login($user);

        return redirect(route('apprenant.dashboard', absolute: false));
    }
}
