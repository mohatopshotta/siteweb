<?php

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

    public static function majIdentite(string $nom, string $prenom): void
    {
        $_SESSION['nom'] = $nom;
        $_SESSION['prenom'] = $prenom;
    }

    public static function exigerConnexion(string $racine): void
    {
        if (!self::estConnecte()) {
            self::rediriger($racine . 'pages/connexion.php');
        }
    }

    public static function exigerRole(array $roles, string $racine): void
    {
        if (!self::estConnecte() || !self::aLeRole($roles)) {
            self::rediriger($racine . 'pages/connexion.php');
        }
    }

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
