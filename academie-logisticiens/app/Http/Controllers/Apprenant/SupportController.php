<?php

namespace App\Http\Controllers\Apprenant;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function index()
    {
        $tickets = Ticket::with('formation')
            ->where('user_id', auth()->id())
            ->orderByDesc('updated_at')
            ->get();

        return view('apprenant.support.index', compact('tickets'));
    }

    public function creer(Request $request)
    {
        $request->validate([
            'sujet' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'formation_id' => 'nullable|exists:formations,id',
        ]);

        Ticket::create([
            'user_id' => auth()->id(),
            'formation_id' => $request->formation_id,
            'sujet' => $request->sujet,
            'message' => $request->message,
        ]);

        return back()->with('success', 'Votre ticket a été créé. L\'équipe vous répondra dans les plus brefs délais.');
    }

    public function repondre(Request $request, Ticket $ticket)
    {
        if (auth()->user()->role !== 'admin' && $ticket->user_id !== auth()->id()) {
            abort(403);
        }

        if ($ticket->statut === 'ferme' && auth()->user()->role !== 'admin') {
            return back()->with('error', 'Ce ticket est ferme. Ouvrez un nouveau ticket pour une autre demande.');
        }

        $request->validate(['message' => 'required|string|max:5000']);

        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'message' => $request->message,
            'is_admin' => false,
        ]);

        return back()->with('success', 'Votre message a été envoyé.');
    }
}
