<?php

/** Gestionnaire / administrateur du site (table gestionnaire). */
class Gestionnaire extends Utilisateur
{
    public function getRole(): string { return 'gestionnaire'; }
    public function getLibelleRole(): string { return 'Gestionnaire'; }
}
