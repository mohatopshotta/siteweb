<?php

class OffreManager extends Manager
{
    private const SELECT_COMPLET = "
        SELECT o.*, e.nom AS entreprise,
               u.nom AS auteur_nom, u.prenom AS auteur_prenom, u.email AS auteur_email
        FROM offre o
        JOIN entreprise e ON e.id_entreprise = o.id_entreprise
        JOIN utilisateur u ON u.id_utilisateur = o.id_auteur
    ";

    public function listerOuvertes(?string $type = null, ?int $limite = null): array
    {
        $sql = self::SELECT_COMPLET . ' WHERE o.etat = ?';
        $params = [Offre::OUVERT];

        if (in_array($type, Offre::TYPES, true)) {
            $sql .= ' AND o.type_offre = ?';
            $params[] = $type;
        }
        $sql .= ' ORDER BY o.date_publication DESC';
        if ($limite !== null) {
            $sql .= ' LIMIT ' . (int)$limite;
        }

        $req = $this->pdo->prepare($sql);
        $req->execute($params);
        return $this->hydraterListe($req->fetchAll(), Offre::class);
    }

    public function listerParEntreprise(int $idEntreprise): array
    {
        $req = $this->pdo->prepare(self::SELECT_COMPLET . ' WHERE o.id_entreprise = ? ORDER BY o.date_publication DESC');
        $req->execute([$idEntreprise]);
        return $this->hydraterListe($req->fetchAll(), Offre::class);
    }

    public function trouverParId(int $idOffre): ?Offre
    {
        $req = $this->pdo->prepare(self::SELECT_COMPLET . ' WHERE o.id_offre = ?');
        $req->execute([$idOffre]);
        $ligne = $req->fetch();
        return $ligne ? new Offre($ligne) : null;
    }

    public function creer(Offre $offre): int
    {
        $req = $this->pdo->prepare(
            'INSERT INTO offre (id_auteur, id_entreprise, titre, description, missions, salaire, type_offre)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $req->execute([
            $offre->getIdAuteur(),
            $offre->getIdEntreprise(),
            $offre->getTitre(),
            $offre->getDescription(),
            $offre->getMissions(),
            $offre->getSalaire(),
            $offre->getTypeOffre(),
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function changerEtat(int $idOffre, string $etat, int $idAuteur): void
    {
        if (!in_array($etat, [Offre::OUVERT, Offre::CLOTURE], true)) {
            return;
        }
        $req = $this->pdo->prepare('UPDATE offre SET etat = ? WHERE id_offre = ? AND id_auteur = ?');
        $req->execute([$etat, $idOffre, $idAuteur]);
    }
}
