<?php

class SpecialiteRepository extends Repository
{
    public function listerTous(): array
    {
        $lignes = $this->pdo->query('SELECT * FROM specialite ORDER BY libelle')->fetchAll();
        return $this->hydraterListe($lignes, NewSpecialite::class);
    }
}
