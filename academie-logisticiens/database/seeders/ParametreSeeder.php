<?php

namespace Database\Seeders;

use App\Models\Parametre;
use Illuminate\Database\Seeder;

class ParametreSeeder extends Seeder
{
    public function run(): void
    {
        $parametres = [
            ['cle' => 'score_minimum_defaut', 'valeur' => '70', 'description' => 'Score minimum de réussite (%)'],
            ['cle' => 'points_test_reussi', 'valeur' => '2', 'description' => 'Points gagnés par test de niveau réussi'],
            ['cle' => 'points_flag_capture', 'valeur' => '5', 'description' => 'Points gagnés par flag capturé'],
            ['cle' => 'seuil_jetons', 'valeur' => '100', 'description' => 'Seuil de points pour conversion en jetons'],
            ['cle' => 'valeur_jetons_fcfa', 'valeur' => '5000', 'description' => 'Valeur en FCFA des jetons à la conversion'],
            ['cle' => 'numero_paiement', 'valeur' => '+242 068 287 915', 'description' => 'Numéro Mobile Money pour les paiements'],
            ['cle' => 'nom_beneficiaire', 'valeur' => 'Didi Chance AMBIA', 'description' => 'Nom du bénéficiaire Mobile Money'],
            ['cle' => 'operateurs_mobile_money', 'valeur' => 'MTN et Airtel', 'description' => 'Opérateurs Mobile Money acceptés'],
            ['cle' => 'delai_validation_paiement', 'valeur' => '30', 'description' => 'Délai max de validation du paiement (minutes)'],
            ['cle' => 'delai_remboursement', 'valeur' => '5', 'description' => 'Délai de remboursement en cas de rejet (minutes)'],
            ['cle' => 'telephone_contact_1', 'valeur' => '+242 06 828 79 15', 'description' => 'Téléphone de contact principal'],
            ['cle' => 'telephone_contact_2', 'valeur' => '+242 05 599 73 34', 'description' => 'Téléphone de contact secondaire'],
            ['cle' => 'email_contact', 'valeur' => 'contact@cabinet-aclog.com', 'description' => 'Email de contact'],
            ['cle' => 'adresse', 'valeur' => '4 Rue Abila, Talangai, Brazzaville', 'description' => 'Adresse du siège'],
            ['cle' => 'chatbot_nom', 'valeur' => 'LogiBot', 'description' => 'Nom du chatbot'],
            ['cle' => 'score_minimum_quiz', 'valeur' => '70', 'description' => 'Score minimum pour passer un quiz de chapitre (%)'],
            ['cle' => 'info_virement_bancaire', 'valeur' => 'Paiement par virement bancaire possible. Contactez-nous pour les coordonnées bancaires.', 'description' => 'Informations sur le virement bancaire'],
        ];

        foreach ($parametres as $param) {
            Parametre::updateOrCreate(['cle' => $param['cle']], $param);
        }
    }
}
