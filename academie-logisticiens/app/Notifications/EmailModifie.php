<?php

namespace App\Notifications;

use App\Models\Parametre;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Envoyee a l'ANCIENNE adresse une fois le changement confirme (alerte de securite). */
class EmailModifie extends Notification
{
    public function __construct(private string $nouvelEmail)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Votre adresse e-mail a été modifiée - ' . Parametre::siteTitre())
            ->line('L\'adresse e-mail de votre compte ' . Parametre::siteTitre() . ' vient d\'être remplacée par ' . $this->nouvelEmail . '.')
            ->line('Si vous n\'êtes pas à l\'origine de ce changement, contactez-nous immédiatement à ' . Parametre::valeur('email_contact', 'contact@cabinet-aclog.com') . '.');
    }
}
