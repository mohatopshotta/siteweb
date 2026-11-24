<?php
// ============================================================
// src/modele/Mail.php
// Envoi de tous les emails du site (mot de passe oublié, candidatures,
// événements, contact...).
//
// En local (MODE_DEV = true) : WAMP n'a pas de serveur de mail, donc chaque
// email est écrit dans le fichier projet/mails_dev.txt (hors du dossier public,
// donc invisible depuis le navigateur, et ignoré par Git).
// On peut ainsi tester et montrer tous les emails sans serveur de mail.
//
// En production (MODE_DEV = false) : l'email est envoyé avec la fonction mail() de PHP.
// ============================================================

class Mail
{
    private const FICHIER_DEV = __DIR__ . '/../../mails_dev.txt';

    public static function envoyer(string $destinataire, string $sujet, string $message): bool
    {
        if (MODE_DEV) {
            return self::ecrireDansFichier($destinataire, $sujet, $message);
        }

        $entetes = "Content-Type: text/plain; charset=UTF-8";
        return (bool)@mail($destinataire, $sujet, $message, $entetes);
    }

    /**
     * Mode développement : ajoute l'email à la fin de mails_dev.txt.
     */
    private static function ecrireDansFichier(string $destinataire, string $sujet, string $message): bool
    {
        $texte = str_repeat('=', 60) . PHP_EOL
            . 'Date : ' . date('d/m/Y H:i:s') . PHP_EOL
            . 'À    : ' . $destinataire . PHP_EOL
            . 'Sujet: ' . $sujet . PHP_EOL
            . str_repeat('-', 60) . PHP_EOL
            . $message . PHP_EOL . PHP_EOL;

        return file_put_contents(self::FICHIER_DEV, $texte, FILE_APPEND) !== false;
    }
}
