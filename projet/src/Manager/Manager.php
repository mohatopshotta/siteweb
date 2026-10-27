<?php

abstract class Manager
{
    protected PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnexion();
    }

    protected function hydraterListe(array $lignes, string $classe): array
    {
        $objets = [];
        foreach ($lignes as $ligne) {
            $objets[] = new $classe($ligne);
        }
        return $objets;
    }
}
