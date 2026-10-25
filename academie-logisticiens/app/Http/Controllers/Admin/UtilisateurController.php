<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UtilisateurController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('is_active', true);

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->q}%")
                  ->orWhere('prenom', 'like', "%{$request->q}%")
                  ->orWhere('email', 'like', "%{$request->q}%");
            });
        }

        $utilisateurs = $query->orderByDesc('created_at')->paginate(20);

        return view('admin.utilisateurs.index', compact('utilisateurs'));
    }

    public function edit(User $user)
    {
        $user->load(['inscriptions.formation', 'paiements', 'certificats', 'progressions']);
        return view('admin.utilisateurs.edit', ['utilisateur' => $user]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:apprenant,admin,gestionnaire',
            'points' => 'integer|min:0',
            'jetons' => 'integer|min:0',
        ]);

        if ($user->id === auth()->id()) {
            unset($data['role']);
        }

        $user->update($data);

        return redirect()->route('admin.utilisateurs.edit', $user)->with('success', 'Utilisateur mis à jour.');
    }

    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'new_password' => ['required', 'string', \Illuminate\Validation\Rules\Password::defaults(), 'confirmed'],
        ]);

        $user->update(['password' => Hash::make($request->new_password)]);

        return redirect()->route('admin.utilisateurs.edit', $user)->with('success', 'Mot de passe réinitialisé.');
    }

    public function suspend(Request $request, User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas suspendre votre propre compte.');
        }

        $request->validate([
            'suspension_type' => 'required|in:temporaire,definitive',
            'suspended_until' => 'required_if:suspension_type,temporaire|nullable|date|after:today',
            'suspension_reason' => 'nullable|string|max:500',
        ]);

        $user->update([
            'suspended_at' => now(),
            'suspended_until' => $request->suspension_type === 'temporaire' ? $request->suspended_until : null,
            'suspension_reason' => $request->suspension_reason,
        ]);

        return redirect()->route('admin.utilisateurs.edit', $user)->with('success', 'Utilisateur suspendu.');
    }

    public function unsuspend(User $user)
    {
        $user->update([
            'suspended_at' => null,
            'suspended_until' => null,
            'suspension_reason' => null,
        ]);

        return redirect()->route('admin.utilisateurs.edit', $user)->with('success', 'Suspension levée.');
    }

    public function softDelete(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas désactiver votre propre compte.');
        }

        $user->update(['is_active' => false]);

        return redirect()->route('admin.utilisateurs.index')->with('success', 'Utilisateur désactivé.');
    }
}
