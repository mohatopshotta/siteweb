<?php

class Session
{
    public static function demarrer(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function connecter(NewUtilisateur $utilisateur): void
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

    // ---------- Protection CSRF ----------
    // Un jeton aléatoire est gardé en session et ajouté dans chaque formulaire.
    // Un formulaire envoyé depuis un autre site n'a pas ce jeton : il est refusé.

    public static function getJetonCsrf(): string
    {
        if (empty($_SESSION['jeton_csrf'])) {
            $_SESSION['jeton_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['jeton_csrf'];
    }

    public static function champCsrf(): string
    {
        return '<input type="hidden" name="jeton_csrf" value="' . self::getJetonCsrf() . '">';
    }

    public static function verifierCsrf(): void
    {
        $jeton = $_POST['jeton_csrf'] ?? '';
        if (!is_string($jeton) || !hash_equals(self::getJetonCsrf(), $jeton)) {
            http_response_code(403);
            exit('Formulaire expiré ou invalide. Reviens en arrière, recharge la page et réessaie.');
        }
    }

    public static function rediriger(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }
}
