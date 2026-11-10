<?php

// Logique du « mot de passe oublié »
class MotDePasseOublie
{
    private ReinitialisationRepository $repository;

    public function __construct()
    {
        $this->repository = new ReinitialisationRepository();
    }

    // Un jeton est 64 caractères hexadécimaux : on refuse tout le reste tout de suite
    public static function jetonAuBonFormat(string $jeton): bool
    {
        return (bool)preg_match('/^[0-9a-f]{64}$/', $jeton);
    }

    // Étape 1 : l'utilisateur donne son email.
    // On ne dit JAMAIS à l'écran si le compte existe (sinon on révèle qui est inscrit).
    public function demander(string $email): void
    {
        $utilisateur = (new UtilisateurRepository())->trouverParEmail($email);
        if ($utilisateur === null) {
            return;
        }

        $jeton = $this->repository->creerJeton($utilisateur->getIdUtilisateur());
        $lien = URL_SITE . '/pages/reinitialiser.php?jeton=' . $jeton;

        $message = "Bonjour " . $utilisateur->getPrenom() . ",\n\n"
            . "Pour choisir un nouveau mot de passe, ouvrez ce lien (valable 1 heure) :\n"
            . $lien . "\n\n"
            . "Si vous n'êtes pas à l'origine de cette demande, ignorez ce message.\n\n"
            . "L'équipe HSP";

        $envoye = Mail::envoyer($utilisateur->getEmail(), 'Réinitialisation de votre mot de passe', $message);

        if (!$envoye) {
            error_log('Mot de passe oublié : email non envoyé pour ' . $utilisateur->getEmail());
        }

        // En local, il n'y a pas de serveur de mail : le lien est écrit dans le fichier projet/mails_dev.txt
        // (hors du dossier public, donc invisible depuis le navigateur).
        // JAMAIS en production (MODE_DEV = false), où le lien donnerait accès au compte.
        if (MODE_DEV) {
            file_put_contents(
                __DIR__ . '/../../mails_dev.txt',
                date('Y-m-d H:i:s') . ' ' . $utilisateur->getEmail() . ' ' . $lien . PHP_EOL,
                FILE_APPEND
            );
        }
    }

    public function jetonValide(string $jeton): bool
    {
        return self::jetonAuBonFormat($jeton) && $this->repository->trouverIdUtilisateur($jeton) !== null;
    }

    // Étape 2 : l'utilisateur a cliqué sur le lien et choisit son nouveau mot de passe.
    // Renvoie la liste des erreurs (tableau vide = mot de passe changé).
    public function reinitialiser(string $jeton, string $motDePasse): array
    {
        $id = self::jetonAuBonFormat($jeton) ? $this->repository->trouverIdUtilisateur($jeton) : null;
        if ($id === null) {
            return ['Lien invalide ou expiré.'];
        }

        // Mêmes règles que l'inscription (12 caractères, majuscule, minuscule, chiffre, caractère spécial).
        // Si le mot de passe est refusé, le jeton n'est pas consommé : l'utilisateur peut réessayer.
        $erreurs = NewUtilisateur::controlerRobustesse($motDePasse);
        if (!empty($erreurs)) {
            return $erreurs;
        }

        $this->repository->changerMotDePasse($id, password_hash($motDePasse, PASSWORD_DEFAULT));
        return [];
    }
}
