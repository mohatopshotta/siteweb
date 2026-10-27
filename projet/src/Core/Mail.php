<?php

/** Envoi des emails du site (candidatures, refus...). */
class Mail
{
    public static function envoyer(string $destinataire, string $sujet, string $message): bool
    {
        $entetes = "Content-Type: text/plain; charset=UTF-8";
        // le @ cache l'erreur si le serveur mail n'est pas configuré (cas fréquent en local)
        return (bool)@mail($destinataire, $sujet, $message, $entetes);
    }
}
