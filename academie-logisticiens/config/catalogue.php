<?php

// Catalogue complet des 40 formations (4 filieres x 10) affiche sur la page Formations.
// Les formations publiees en base (programme detaille + paiement en ligne) s'affichent en tete de leur filiere ;
// les autres renvoient vers une inscription accompagnee sur WhatsApp tant qu'elles ne sont pas creees dans l'admin.
// 'cle' = valeur du champ categorie des formations en base ; 'image' = identifiant de photo Unsplash.

return [
    [
        'cle' => 'logistique',
        'nom' => 'Logistique',
        'couleur' => '#0E3A6B',
        'formations' => [
            ['titre' => 'Fondamentaux de la logistique et de l\'entreprise', 'prix' => 150000, 'description' => 'Comprenez le rôle de la logistique dans l\'entreprise et maîtrisez les concepts clés de la chaîne logistique.', 'image' => '1553413077-190dd305871c'],
            ['titre' => 'Gestion des stocks', 'prix' => 180000, 'description' => 'Optimisez vos niveaux de stock, réduisez les ruptures et maîtrisez les coûts de stockage.', 'image' => '1587293852726-70cdb56c2866'],
            ['titre' => 'Gestion des approvisionnements', 'prix' => 180000, 'description' => 'Planifiez et sécurisez vos approvisionnements pour garantir la continuité de l\'activité.', 'image' => '1672552226380-486fe900b322'],
            ['titre' => 'Supply Chain Management', 'prix' => 250000, 'description' => 'Pilotez toute la chaîne d\'approvisionnement, des fournisseurs jusqu\'au client final.', 'image' => '1678182451047-196f22a4143e'],
            ['titre' => 'Gestion d\'entrepôt et magasinage', 'prix' => 200000, 'description' => 'Organisez un entrepôt performant : réception, rangement, préparation de commandes et expédition.', 'image' => '1627309366653-2dedc084cdf1'],
            ['titre' => 'Logistique de distribution', 'prix' => 200000, 'description' => 'Maîtrisez les flux de distribution et la livraison pour améliorer la qualité de service.', 'image' => '1620455800201-7f00aeef12ed'],
            ['titre' => 'Achats et gestion des fournisseurs', 'prix' => 180000, 'description' => 'Choisissez, évaluez et négociez avec vos fournisseurs pour sécuriser vos achats.', 'image' => '1758519288905-38b7b00c1023'],
            ['titre' => 'Planification et prévision de la demande', 'prix' => 220000, 'description' => 'Anticipez la demande et planifiez vos ressources pour mieux répondre au marché.', 'image' => '1551288049-bebda4e38f71'],
            ['titre' => 'Traçabilité et systèmes d\'information logistique', 'prix' => 230000, 'description' => 'Suivez vos marchandises en temps réel grâce aux outils et systèmes d\'information logistiques.', 'image' => '1526628953301-3e589a6a8b74'],
            ['titre' => 'Logistique humanitaire et de projets', 'prix' => 200000, 'description' => 'Organisez la logistique des opérations humanitaires et des projets en contexte exigeant.', 'image' => '1637034132661-4f015591b84a'],
        ],
    ],
    [
        'cle' => 'transport',
        'nom' => 'Transport',
        'couleur' => '#3F7BB5',
        'formations' => [
            ['titre' => 'Introduction générale au transport', 'prix' => 150000, 'description' => 'Découvrez les modes de transport, leurs acteurs et leur rôle dans l\'économie.', 'image' => '1592805144716-feeccccef5ac'],
            ['titre' => 'Transport aérien : réglementation et organisation', 'prix' => 280000, 'description' => 'Appréhendez la réglementation et l\'organisation du transport aérien de passagers et de fret.', 'image' => '1569154941061-e231b4725ef1'],
            ['titre' => 'Transport maritime et portuaire', 'prix' => 280000, 'description' => 'Comprenez le fonctionnement des navires, des ports et des opérations maritimes.', 'image' => '1590496793907-4d66e2994b4d'],
            ['titre' => 'Transport routier de marchandises', 'prix' => 200000, 'description' => 'Organisez le transport routier de marchandises : tournées, coûts, règles et sécurité.', 'image' => '1591768793355-74d04bb6608f'],
            ['titre' => 'Transport multimodal et conteneurisation', 'prix' => 250000, 'description' => 'Combinez les modes de transport et maîtrisez la chaîne du conteneur.', 'image' => '1601897690942-bcacbad33e55'],
            ['titre' => 'Exploitation aéroportuaire', 'prix' => 300000, 'description' => 'Découvrez l\'exploitation d\'un aéroport : escales, sûreté et services au sol.', 'image' => '1466691623998-d607fab1ca29'],
            ['titre' => 'Transport de marchandises dangereuses et de produits liquides', 'prix' => 300000, 'description' => 'Transportez en sécurité les marchandises dangereuses et les produits liquides, dans le respect des règles.', 'image' => '1649372708980-1efce0ffb7bb'],
            ['titre' => 'Gestion de flotte et maintenance', 'prix' => 220000, 'description' => 'Gérez votre flotte de véhicules et organisez la maintenance pour réduire les pannes.', 'image' => '1727893119356-1702fe921cf9'],
            ['titre' => 'Affrètement et courtage de transport', 'prix' => 230000, 'description' => 'Maîtrisez l\'affrètement, le courtage et la contractualisation du transport.', 'image' => '1606185540834-d6e7483ee1a4'],
            ['titre' => 'Transport ferroviaire et fluvial', 'prix' => 200000, 'description' => 'Explorez le transport ferroviaire et fluvial, leurs atouts et leurs contraintes.', 'image' => '1761208986981-f3fad9aa4514'],
        ],
    ],
    [
        'cle' => 'commerce',
        'nom' => 'Commerce international',
        'couleur' => '#C08A2B',
        'formations' => [
            ['titre' => 'Commerce international et Incoterms', 'prix' => 200000, 'description' => 'Maîtrisez les Incoterms et les bases des opérations de commerce international.', 'image' => '1765206257996-9b4a5d886a2c'],
            ['titre' => 'Techniques de négociation commerciale', 'prix' => 180000, 'description' => 'Préparez et conduisez vos négociations commerciales avec méthode et confiance.', 'image' => '1521791136064-7986c2920216'],
            ['titre' => 'Moyens de paiement internationaux', 'prix' => 220000, 'description' => 'Sécurisez vos règlements internationaux grâce aux bons moyens de paiement.', 'image' => '1706814901248-5ac8b312c5f3'],
            ['titre' => 'Crédit documentaire', 'prix' => 250000, 'description' => 'Comprenez et utilisez le crédit documentaire pour garantir vos transactions.', 'image' => '1554224155-6726b3ff858f'],
            ['titre' => 'Import-export : montage d\'opérations', 'prix' => 250000, 'description' => 'Montez et suivez vos opérations d\'import-export de bout en bout.', 'image' => '1759272840538-ae4b07214c71'],
            ['titre' => 'Marketing international', 'prix' => 200000, 'description' => 'Analysez les marchés étrangers et construisez votre stratégie à l\'international.', 'image' => '1557804506-669a67965ba0'],
            ['titre' => 'Assurance transport et gestion des risques', 'prix' => 220000, 'description' => 'Identifiez les risques du transport et choisissez la couverture d\'assurance adaptée.', 'image' => '1590497008432-598f04441de8'],
            ['titre' => 'Contrats internationaux', 'prix' => 250000, 'description' => 'Rédigez et négociez des contrats internationaux solides et sécurisés.', 'image' => '1562564055-71e051d33c19'],
            ['titre' => 'Prospection des marchés africains (ZLECAf)', 'prix' => 230000, 'description' => 'Repérez les opportunités des marchés africains et ouvrez-vous à la ZLECAf.', 'image' => '1777065851469-71aef898a26f'],
            ['titre' => 'E-commerce et commerce transfrontalier', 'prix' => 180000, 'description' => 'Développez vos ventes en ligne et gérez les échanges transfrontaliers.', 'image' => '1566576721346-d4a3b4eaeb55'],
        ],
    ],
    [
        'cle' => 'douane',
        'nom' => 'Douane et transit',
        'couleur' => '#1B5393',
        'formations' => [
            ['titre' => 'Procédures de dédouanement', 'prix' => 200000, 'description' => 'Maîtrisez les étapes du dédouanement, des documents au paiement des droits.', 'image' => '1700716465891-9e5e9f501d7d'],
            ['titre' => 'Régimes douaniers', 'prix' => 220000, 'description' => 'Choisissez le régime douanier adapté à chaque opération.', 'image' => '1634646809203-f3b4adff9127'],
            ['titre' => 'Transit et transport inter-États (CEMAC)', 'prix' => 230000, 'description' => 'Organisez le transit et le transport entre États, dans l\'espace CEMAC.', 'image' => '1631744591853-998c4308bbb0'],
            ['titre' => 'Déclaration en douane et SYDONIA', 'prix' => 250000, 'description' => 'Établissez vos déclarations en douane et utilisez le système SYDONIA.', 'image' => '1460925895917-afdab827c52f'],
            ['titre' => 'Nomenclature et classement tarifaire (SH)', 'prix' => 220000, 'description' => 'Classez correctement vos marchandises avec le Système Harmonisé.', 'image' => '1586528243235-c6e6c4a4ccbf'],
            ['titre' => 'Valeur en douane et origine des marchandises', 'prix' => 230000, 'description' => 'Déterminez la valeur en douane et l\'origine des marchandises.', 'image' => '1709880945165-d2208c6ad2ec'],
            ['titre' => 'Contentieux douanier', 'prix' => 250000, 'description' => 'Comprenez les infractions douanières et gérez les litiges avec l\'administration.', 'image' => '1589391886645-d51941baf7fb'],
            ['titre' => 'Fiscalité douanière et exonérations', 'prix' => 220000, 'description' => 'Calculez droits et taxes, et comprenez les exonérations applicables.', 'image' => '1633158829585-23ba8f7c8caf'],
            ['titre' => 'Commissionnaire agréé en douane', 'prix' => 250000, 'description' => 'Découvrez le métier et les responsabilités du commissionnaire agréé en douane.', 'image' => '1681505504714-4ded1bc247e7'],
            ['titre' => 'Contrôle, conformité et sûreté aux frontières', 'prix' => 260000, 'description' => 'Assurez la conformité de vos opérations et la sûreté aux frontières.', 'image' => '1628348068343-c6a848d2b6dd'],
        ],
    ],
];
