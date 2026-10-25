<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Formation;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function handle(Request $request)
    {
        $request->validate(['question' => 'required|string|max:500']);

        $question = mb_strtolower($request->question);
        $reponse = $this->trouverReponse($question);

        return response()->json(['reponse' => $reponse]);
    }

    private function trouverReponse(string $question): string
    {
        if (str_contains($question, 'formation') || str_contains($question, 'cours') || str_contains($question, 'programme')) {
            $formations = Formation::where('is_published', true)->take(5)->pluck('titre')->toArray();
            $liste = implode(', ', $formations);
            return "Nous proposons plusieurs formations certifiantes : {$liste}. Consultez notre catalogue complet sur la page Formations pour découvrir tous les détails et tarifs.";
        }

        if (str_contains($question, 'prix') || str_contains($question, 'tarif') || str_contains($question, 'coût') || str_contains($question, 'combien')) {
            return "Nos tarifs varient selon la formation choisie. Les prix sont affichés sur chaque fiche formation dans le catalogue. Nous acceptons les paiements par Mobile Money (MTN et Airtel). Des codes promo sont disponibles pour les premières inscriptions !";
        }

        if (str_contains($question, 'paiement') || str_contains($question, 'payer') || str_contains($question, 'mobile money') || str_contains($question, 'mtn') || str_contains($question, 'airtel')) {
            return "Le paiement se fait par Mobile Money (MTN Mobile Money ou Airtel Money). Après votre transfert, téléchargez la capture d'écran de confirmation sur la plateforme. Notre équipe valide votre paiement sous 30 minutes maximum.";
        }

        if (str_contains($question, 'inscrire') || str_contains($question, 'inscription') || str_contains($question, 'compte') || str_contains($question, 'commencer')) {
            return "Pour vous inscrire, cliquez sur 'S'inscrire' en haut de la page. Remplissez le formulaire avec vos informations, puis choisissez une formation dans le catalogue. L'inscription est gratuite, vous ne payez que pour accéder aux formations.";
        }

        if (str_contains($question, 'certificat') || str_contains($question, 'diplôme') || str_contains($question, 'attestation')) {
            return "À la fin de chaque formation, après avoir réussi l'examen final (score minimum requis), vous recevez un certificat numérique avec un QR code de vérification. Ce certificat est téléchargeable en PDF et vérifiable en ligne par tout employeur.";
        }

        if (str_contains($question, 'challenge') || str_contains($question, 'ctf') || str_contains($question, 'flag') || str_contains($question, 'défi')) {
            return "Nos Challenges CTF (Capture The Flag) sont des exercices pratiques de logistique. Résolvez des scénarios réels (blocage douanier, anomalie conteneur...) et gagnez +5 points par flag capturé. Accumulez 100 points pour convertir en 5 000 FCFA de réduction !";
        }

        if (str_contains($question, 'point') || str_contains($question, 'jeton') || str_contains($question, 'récompense') || str_contains($question, 'gamification')) {
            return "Chaque action est récompensée : +2 points par test de niveau, +5 points par flag CTF capturé. À 100 points, vous pouvez convertir en 5 000 FCFA de jetons déductibles sur votre prochaine formation !";
        }

        if (str_contains($question, 'contact') || str_contains($question, 'joindre') || str_contains($question, 'téléphone') || str_contains($question, 'adresse')) {
            return "Vous pouvez nous contacter via la page Contact du site, par email ou par téléphone. Notre équipe basée à Brazzaville est disponible du lundi au vendredi de 8h à 18h et le samedi de 9h à 13h.";
        }

        if (str_contains($question, 'bonjour') || str_contains($question, 'salut') || str_contains($question, 'hello') || str_contains($question, 'bonsoir')) {
            return "Bonjour ! Bienvenue sur " . \App\Models\Parametre::siteTitre() . ". Comment puis-je vous aider ? Je peux vous renseigner sur nos formations, tarifs, paiements, certifications ou challenges CTF.";
        }

        if (str_contains($question, 'merci') || str_contains($question, 'thanks')) {
            return "Avec plaisir ! N'hésitez pas si vous avez d'autres questions. Bonne exploration de nos formations !";
        }

        return "Je suis l'assistant IA de " . \App\Models\Parametre::siteTitre() . ". Je peux vous aider avec : nos formations, les tarifs, le paiement Mobile Money, les certificats, les challenges CTF, ou le processus d'inscription. Que souhaitez-vous savoir ?";
    }
}
