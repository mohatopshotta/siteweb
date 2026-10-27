<?php

/**
 * Gestion de la session de l'utilisateur connecté.
 *
 * Une seule convention pour tout le site :
 *   $_SESSION['id_utilisateur'], $_SESSION['nom'], $_SESSION['prenom'], $_SESSION['role']
 * role = 'etudiant' | 'medecin' | 'partenaire' | 'gestionnaire'
 *
 * Les méthodes "exiger..." redirigent vers la page de connexion si l'accès est refusé.
 * $racine = chemin relatif vers public/ (même convention que header.php).
 */
class Session
{
    public static function demarrer(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function connecter(Utilisateur $utilisateur): void
    {
        // nouvel identifiant de session à la connexion (évite le vol de session)
        session_regenerate_id(true);
        $_SESSION['id_utilisateur'] = $utilisateur->getIdUtilisateur();
        $_SESSION['nom'] = $utilisateur->getNom();
        $_SESSION['prenom'] = $utilisateur->getPrenom();
        $_SESSION['role'] = $utilisateur->getRole();
    }

    public static function deconnecter(): void
    {
        session_unset();
        session_destroy();
    }

    public static function estConnecte(): bool
    {
        return isset($_SESSION['id_utilisateur']);
    }

    public static function getIdUtilisateur(): ?int
    {
        return isset($_SESSION['id_utilisateur']) ? (int)$_SESSION['id_utilisateur'] : null;
    }

    public static function getRole(): ?string
    {
        return $_SESSION['role'] ?? null;
    }

    public static function aLeRole(array $roles): bool
    {
        return in_array(self::getRole(), $roles, true);
    }

    /** Met à jour le nom/prénom gardés en session (après modification du profil). */
    public static function majIdentite(string $nom, string $prenom): void
    {
        $_SESSION['nom'] = $nom;
        $_SESSION['prenom'] = $prenom;
    }

    /** Page réservée aux utilisateurs connectés. */
    public static function exigerConnexion(string $racine): void
    {
        if (!self::estConnecte()) {
            self::rediriger($racine . 'pages/connexion.php');
        }
    }

    /** Page réservée à certains rôles. */
    public static function exigerRole(array $roles, string $racine): void
    {
        if (!self::estConnecte() || !self::aLeRole($roles)) {
            self::rediriger($racine . 'pages/connexion.php');
        }
    }

    /** Page réservée aux membres (tout le monde sauf le gestionnaire). */
    public static function exigerMembre(string $racine): void
    {
        self::exigerRole(['etudiant', 'medecin', 'partenaire'], $racine);
    }

    public static function rediriger(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }
}
