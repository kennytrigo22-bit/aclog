<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = $request->user();

        if (!$user->is_active) {
            Auth::logout();
            return back()->withErrors(['email' => 'Ce compte a été désactivé.']);
        }

        if ($user->isSuspended()) {
            $msg = 'Votre compte est suspendu.';
            if ($user->suspended_until) {
                $msg .= ' Jusqu\'au ' . $user->suspended_until->format('d/m/Y') . '.';
            }
            Auth::logout();
            return back()->withErrors(['email' => $msg]);
        }

        $request->session()->regenerate();

        $defaultRoute = match ($user->role) {
            'admin', 'gestionnaire' => route('admin.dashboard', absolute: false),
            default => route('apprenant.dashboard', absolute: false),
        };

        return redirect()->intended($defaultRoute);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
