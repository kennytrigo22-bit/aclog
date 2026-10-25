<?php

namespace App\Notifications;

use App\Models\Paiement;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaiementValide extends Notification
{
    use Queueable;

    public function __construct(public Paiement $paiement) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Paiement validé — ' . \App\Models\Parametre::siteTitre())
            ->greeting('Bonjour ' . ($notifiable->prenom ?? $notifiable->name) . ',')
            ->line('Votre paiement de **' . number_format($this->paiement->montant, 0, ',', ' ') . ' FCFA** pour la formation **' . $this->paiement->formation->titre . '** a été validé avec succès.')
            ->line('Vous pouvez maintenant accéder à tous les modules de cette formation depuis votre espace apprenant.')
            ->action('Accéder à mes formations', url('/espace/mes-formations'))
            ->line('Bonne formation !');
    }
}
