<?php

class EtablissementManager extends Manager
{
    /** @return Etablissement[] */
    public function listerTous(): array
    {
        $lignes = $this->pdo->query('SELECT * FROM etablissement ORDER BY nom')->fetchAll();
        return $this->hydraterListe($lignes, Etablissement::class);
    }

    /**
     * Renvoie l'id de l'établissement qui a ce site web, ou l'ajoute s'il n'existe pas encore (anti-doublon).
     */
    public function trouverOuCreer(Etablissement $etablissement): int
    {
        $req = $this->pdo->prepare('SELECT id_etablissement FROM etablissement WHERE site_web = ?');
        $req->execute([$etablissement->getSiteWeb()]);
        $id = $req->fetchColumn();
        if ($id) {
            return (int)$id;
        }

        $req = $this->pdo->prepare('INSERT INTO etablissement (nom, adresse, site_web) VALUES (?, ?, ?)');
        $req->execute([$etablissement->getNom(), $etablissement->getAdresse(), $etablissement->getSiteWeb()]);
        return (int)$this->pdo->lastInsertId();
    }
}
