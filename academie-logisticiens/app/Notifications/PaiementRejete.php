<?php

namespace App\Notifications;

use App\Models\Paiement;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaiementRejete extends Notification
{
    use Queueable;

    public function __construct(public Paiement $paiement) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Paiement non validé — ' . \App\Models\Parametre::siteTitre())
            ->greeting('Bonjour ' . ($notifiable->prenom ?? $notifiable->name) . ',')
            ->line('Votre paiement de **' . number_format($this->paiement->montant, 0, ',', ' ') . ' FCFA** pour la formation **' . $this->paiement->formation->titre . '** n\'a pas pu être validé.');

        if ($this->paiement->note_admin) {
            $mail->line('**Motif :** ' . $this->paiement->note_admin);
        }

        return $mail
            ->line('Si vous pensez qu\'il s\'agit d\'une erreur, vous pouvez soumettre un nouveau paiement ou contacter le support.')
            ->action('Contacter le support', url('/espace/support'));
    }
}
