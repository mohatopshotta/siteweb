<?php

/**
 * Classe mère de tous les managers.
 * Un manager contient toutes les requêtes SQL d'une table : les pages n'écrivent plus de SQL.
 */
abstract class Manager
{
    protected PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnexion();
    }

    /**
     * Transforme une liste de lignes SQL en liste d'objets de la classe donnée.
     * Exemple : $this->hydraterListe($lignes, Offre::class) -> Offre[]
     */
    protected function hydraterListe(array $lignes, string $classe): array
    {
        $objets = [];
        foreach ($lignes as $ligne) {
            $objets[] = new $classe($ligne);
        }
        return $objets;
    }
}
