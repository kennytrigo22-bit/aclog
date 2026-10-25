<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Parametre;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        return view('public.contact');
    }

    public function envoyer(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'objet' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        try {
            \Illuminate\Support\Facades\Mail::raw(
                "Nouveau message de contact\n\nNom: {$request->nom}\nEmail: {$request->email}\nObjet: {$request->objet}\n\nMessage:\n{$request->message}",
                function ($mail) use ($request) {
                    $mail->to(Parametre::valeur('email_contact', 'contact@cabinet-aclog.com'))
                         ->subject('Contact: ' . $request->objet)
                         ->replyTo($request->email, $request->nom);
                }
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Contact mail failed', ['error' => $e->getMessage(), 'from' => $request->email, 'objet' => $request->objet, 'message' => $request->message]);

            return back()->withInput()->with('error', 'Envoi impossible pour le moment. Merci de reessayer plus tard ou de nous contacter par telephone.');
        }

        return back()->with('success', 'Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.');
    }
}
