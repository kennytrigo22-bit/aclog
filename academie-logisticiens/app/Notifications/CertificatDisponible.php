<?php

namespace App\Notifications;

use App\Models\Certificat;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CertificatDisponible extends Notification
{
    use Queueable;

    public function __construct(public Certificat $certificat) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Votre certificat est disponible — ' . \App\Models\Parametre::siteTitre())
            ->greeting('Félicitations ' . ($notifiable->prenom ?? $notifiable->name) . ' !')
            ->line('Vous avez terminé avec succès la formation **' . $this->certificat->formation->titre . '** avec un score de **' . $this->certificat->score . '%**.')
            ->line('Votre certificat est maintenant disponible au téléchargement, avec un QR code de vérification unique.')
            ->action('Télécharger mon certificat', url('/espace/certifications'))
            ->line('Code de vérification : **' . $this->certificat->code_verification . '**');
    }
}
