<?php

namespace App\Notifications;

use App\Models\Parametre;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

/** Envoyee a la NOUVELLE adresse : le lien signe (valable 60 min) valide le changement. */
class ConfirmerNouvelEmail extends Notification
{
    public function __construct(private User $user, private string $nouvelEmail)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $lien = URL::temporarySignedRoute('profile.email.confirm', now()->addMinutes(60), [
            'user' => $this->user->id,
            'email' => $this->nouvelEmail,
        ]);

        return (new MailMessage)
            ->subject('Confirmez votre nouvelle adresse e-mail - ' . Parametre::siteTitre())
            ->greeting('Bonjour ' . ($this->user->prenom ?: $this->user->name) . ',')
            ->line('Vous avez demandé à utiliser cette adresse pour votre compte ' . Parametre::siteTitre() . '.')
            ->action('Confirmer ma nouvelle adresse', $lien)
            ->line('Ce lien est valable 60 minutes. Tant qu\'il n\'est pas utilisé, votre ancienne adresse reste active.')
            ->line('Si vous n\'êtes pas à l\'origine de cette demande, ignorez simplement ce message.');
    }
}
