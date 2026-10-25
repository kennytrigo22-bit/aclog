<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BienvenueApprenant extends Notification
{
    use Queueable;

    public function __construct() {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Bienvenue chez ' . \App\Models\Parametre::siteTitre() . ' !')
            ->greeting('Bienvenue ' . ($notifiable->prenom ?? $notifiable->name) . ' !')
            ->line('Votre compte a été créé avec succès sur la plateforme ' . \App\Models\Parametre::siteTitre() . ' de Brazzaville.')
            ->line('Voici ce que vous pouvez faire :')
            ->line('- **Explorer** notre catalogue de formations certifiantes')
            ->line('- **Passer** un test de niveau gratuit (+2 points)')
            ->line('- **Relever** des challenges CTF (+5 points/flag)')
            ->line('- **Convertir** vos points en jetons FCFA (100 pts = 5 000 FCFA)')
            ->action('Explorer les formations', url('/formations'))
            ->line('À bientôt sur la plateforme !');
    }
}
