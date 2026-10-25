<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $query = Ticket::with('user');

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        $tickets = $query->orderByDesc('updated_at')->paginate(20);

        return view('admin.tickets.index', compact('tickets'));
    }

    public function show(Ticket $ticket)
    {
        $ticket->load(['user', 'formation', 'messages']);
        return view('admin.tickets.show', compact('ticket'));
    }

    public function repondre(Request $request, Ticket $ticket)
    {
        $request->validate(['message' => 'required|string|max:5000']);

        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'message' => $request->message,
            'is_admin' => true,
        ]);

        if ($ticket->statut === 'ouvert') {
            $ticket->update(['statut' => 'en_cours']);
        }

        return back()->with('success', 'Réponse envoyée.');
    }

    public function changerStatut(Request $request, Ticket $ticket)
    {
        $request->validate(['statut' => 'required|in:ouvert,en_cours,resolu,ferme']);
        $ticket->update(['statut' => $request->statut]);
        return back()->with('success', 'Statut mis à jour.');
    }
}
