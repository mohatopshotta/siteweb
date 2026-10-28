<?php

class NewGestionnaire extends NewUtilisateur
{
    public function getRole(): string { return 'gestionnaire'; }
    public function getLibelleRole(): string { return 'Gestionnaire';; }
}
