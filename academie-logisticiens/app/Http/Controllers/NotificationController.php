<?php

namespace App\Http\Controllers;

use App\Models\NotificationInterne;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = NotificationInterne::where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(NotificationInterne $notificationInterne)
    {
        if ($notificationInterne->user_id !== auth()->id()) {
            abort(403);
        }

        $notificationInterne->update(['is_read' => true]);

        return back()->with('success', 'Notification marquée comme lue.');
    }

    public function markAllAsRead()
    {
        NotificationInterne::where('user_id', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return back()->with('success', 'Toutes les notifications marquées comme lues.');
    }
}
