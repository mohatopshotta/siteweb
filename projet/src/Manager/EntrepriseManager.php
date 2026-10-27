<?php

class EntrepriseManager extends Manager
{
    /** @return Entreprise[] */
    public function listerTous(): array
    {
        $lignes = $this->pdo->query('SELECT * FROM entreprise ORDER BY nom')->fetchAll();
        return $this->hydraterListe($lignes, Entreprise::class);
    }

    /**
     * Renvoie l'id de l'entreprise qui a ce site web, ou l'ajoute si elle n'existe pas encore (anti-doublon).
     */
    public function trouverOuCreer(Entreprise $entreprise): int
    {
        $req = $this->pdo->prepare('SELECT id_entreprise FROM entreprise WHERE site_web = ?');
        $req->execute([$entreprise->getSiteWeb()]);
        $id = $req->fetchColumn();
        if ($id) {
            return (int)$id;
        }

        $req = $this->pdo->prepare('INSERT INTO entreprise (nom, adresse, site_web) VALUES (?, ?, ?)');
        $req->execute([$entreprise->getNom(), $entreprise->getAdresse(), $entreprise->getSiteWeb()]);
        return (int)$this->pdo->lastInsertId();
    }
}
