<?php

class SpecialiteRepository extends Repository
{
    public function ajouter(string $libelle): string
    {
        $libelle = trim($libelle);
        if ($libelle === '' || mb_strlen($libelle) > 100) {
            return 'Le libellé est obligatoire (100 caractères maximum).';
        }
        $req = $this->pdo->prepare('SELECT 1 FROM specialite WHERE libelle = ?');
        $req->execute([$libelle]);
        if ($req->fetchColumn()) {
            return 'Cette spécialité existe déjà.';
        }
        $req = $this->pdo->prepare('INSERT INTO specialite (libelle) VALUES (?)');
        $req->execute([$libelle]);
        return '';
    }

    public function supprimer(int $idSpecialite): string
    {
        $req = $this->pdo->prepare('SELECT 1 FROM medecin WHERE id_specialite = ?');
        $req->execute([$idSpecialite]);
        if ($req->fetchColumn()) {
            return 'Cette spécialité est utilisée par des médecins : impossible de la supprimer.';
        }
        try {
            $req = $this->pdo->prepare('DELETE FROM specialite WHERE id_specialite = ?');
            $req->execute([$idSpecialite]);
            return '';
        } catch (PDOException $e) {
            return 'Cette spécialité est utilisée par des médecins : impossible de la supprimer.';
        }
    }

    public function listerTous(): array
    {
        $lignes = $this->pdo->query('SELECT * FROM specialite ORDER BY libelle')->fetchAll();
        return $this->hydraterListe($lignes, NewSpecialite::class);
    }
}
