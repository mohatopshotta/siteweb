<?php
// ============================================================
// src/Core/InfosSite.php
// Informations légales du site, utilisées par les pages RGPD
// (mentions légales + politique de confidentialité).
// On modifie une info ici : elle change sur toutes les pages.
// Les valeurs entre [crochets] sont à compléter avant la mise en ligne.
// ============================================================

class InfosSite
{
    // ---------- Éditeur ----------
    public const NOM_SITE    = 'HSP';
    public const EDITEUR     = 'Groupe hospitalier GDH';
    public const ETABLISSEMENT = 'Hôpital Sud Paris (HSP)';
    public const ADRESSE     = '[adresse du siège]';
    public const TELEPHONE   = '[numéro]';
    public const EMAIL       = '[adresse email de contact]';
    public const DIRECTEUR_PUBLICATION = '[Nom et fonction du directeur de la publication]';

    // ---------- Hébergeur ----------
    public const HEBERGEUR_NOM       = "[Nom de l'hébergeur]";
    public const HEBERGEUR_ADRESSE   = "[Adresse de l'hébergeur]";
    public const HEBERGEUR_TELEPHONE = "[Téléphone de l'hébergeur]";

    // ---------- RGPD ----------
    public const EMAIL_DPO = '[adresse email du DPO ou du responsable]';
    public const ANNEES_CONSERVATION_INACTIF = 3; // à valider par le groupe
    public const DELAI_REPONSE = 'un mois';

    /**
     * Données collectées, par type d'utilisateur.
     * @return array<string, string>
     */
    public static function getDonneesCollectees(): array
    {
        return [
            'Tous les utilisateurs'          => 'Nom, prénom, adresse email, mot de passe (enregistré haché, jamais en clair), date d\'inscription, date de dernière connexion, date d\'acceptation de cette politique',
            'Étudiants'                      => 'Établissement, formation, CV (si vous le déposez)',
            'Médecins'                       => 'Spécialité, hôpital, établissement',
            'Partenaires'                    => 'Entreprise, poste occupé',
            'Selon votre utilisation du site' => 'Candidatures (lettre de motivation, date, statut), inscriptions aux événements, messages et réponses publiés sur le forum, offres publiées',
        ];
    }

    /**
     * Pourquoi les données sont utilisées.
     * @return string[]
     */
    public static function getFinalites(): array
    {
        return [
            'Créer et gérer votre compte, et vérifier votre profil avant de l\'activer',
            'Mettre en relation les étudiants, les médecins et les entreprises partenaires',
            'Gérer les offres, les candidatures et les inscriptions aux événements',
            'Faire fonctionner le forum',
            'Assurer la sécurité du site',
        ];
    }

    /**
     * Qui peut voir les données.
     * @return array<string, string>
     */
    public static function getDestinataires(): array
    {
        return [
            'Les gestionnaires du site' => 'ils voient les informations de votre compte pour le valider ou le refuser',
            'L\'auteur d\'une offre'    => 'il voit les candidatures envoyées à son offre : votre nom, votre adresse email et votre lettre de motivation',
            'Les autres utilisateurs'   => 'ils voient seulement ce que vous publiez (messages du forum, offres, événements) avec votre nom',
        ];
    }

    /**
     * Durées de conservation.
     * @return array<string, string>
     */
    public static function getDureesConservation(): array
    {
        return [
            'Compte refusé par un gestionnaire'               => 'Supprimé immédiatement',
            'Compte actif'                                    => 'Tant que le compte est utilisé, puis supprimé après ' . self::ANNEES_CONSERVATION_INACTIF . ' ans sans connexion (gestionnaires exclus)',
            'CV, candidatures, inscriptions, messages du forum' => 'Supprimés en même temps que le compte',
        ];
    }

    /**
     * Droits RGPD de l'utilisateur.
     * @return string[]
     */
    public static function getDroits(): array
    {
        return [
            'accéder à vos données et en obtenir une copie',
            'les faire corriger si elles sont fausses',
            'supprimer votre compte et toutes vos données, vous-même depuis la page « Mon profil »',
            'vous opposer à leur utilisation ou demander à la limiter',
            'récupérer vos données dans un format lisible (portabilité)',
        ];
    }

    /**
     * Date de dernière modification d'un fichier, au format français (ex : 27/10/2026).
     */
    public static function dateMiseAJour(string $fichier): string
    {
        return date('d/m/Y', filemtime($fichier));
    }
}
