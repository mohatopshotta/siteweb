<?php

class Mail
{
    public static function envoyer(string $destinataire, string $sujet, string $message): bool
    {
        $entetes = "Content-Type: text/plain; charset=UTF-8";

        return (bool)@mail($destinataire, $sujet, $message, $entetes);
    }
}
