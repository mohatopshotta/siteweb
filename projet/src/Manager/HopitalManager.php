<?php

class HopitalManager extends Manager
{
    public function listerTous(): array
    {
        $lignes = $this->pdo->query('SELECT * FROM hopital ORDER BY nom')->fetchAll();
        return $this->hydraterListe($lignes, Hopital::class);
    }
}
