<?php

class EvenementRepository extends Repository
{
    private const SELECT_AVEC_PLACES = "
        SELECT e.*, COUNT(ie.id_utilisateur) AS places_prises
        FROM evenement e
        LEFT JOIN inscription_evenement ie
               ON ie.id_evenement = e.id_evenement AND ie.organiser = 0
    ";

    public function listerTous(?int $limite = null): array
    {
        $sql = self::SELECT_AVEC_PLACES . ' GROUP BY e.id_evenement ORDER BY e.id_evenement DESC';
        if ($limite !== null) {
            $sql .= ' LIMIT ' . (int)$limite;
        }
        $lignes = $this->pdo->query($sql)->fetchAll();
        return $this->hydraterListe($lignes, NewEvenement::class);
    }

    public function trouverParId(int $idEvenement): ?NewEvenement
    {
        $req = $this->pdo->prepare(self::SELECT_AVEC_PLACES . ' WHERE e.id_evenement = ? GROUP BY e.id_evenement');
        $req->execute([$idEvenement]);
        $ligne = $req->fetch();
        return $ligne ? new NewEvenement($ligne) : null;
    }

    public function creer(NewEvenement $evenement, int $idOrganisateur): int
    {
        $this->pdo->beginTransaction();
        try {
            $req = $this->pdo->prepare(
                'INSERT INTO evenement (type_evenement, titre, description, lieu, elements_requis, nombre_places)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $req->execute([
                $evenement->getTypeEvenement(),
                $evenement->getTitre(),
                $evenement->getDescription(),
                $evenement->getLieu(),
                $evenement->getElementsRequis(),
                $evenement->getNombrePlaces(),
            ]);
            $id = (int)$this->pdo->lastInsertId();

            $req = $this->pdo->prepare(
                'INSERT INTO inscription_evenement (id_utilisateur, id_evenement, organiser) VALUES (?, ?, 1)'
            );
            $req->execute([$idOrganisateur, $id]);

            $this->pdo->commit();
            return $id;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function modifier(NewEvenement $evenement): void
    {
        $req = $this->pdo->prepare(
            'UPDATE evenement
             SET type_evenement = ?, titre = ?, description = ?, lieu = ?, elements_requis = ?, nombre_places = ?
             WHERE id_evenement = ?'
        );
        $req->execute([
            $evenement->getTypeEvenement(),
            $evenement->getTitre(),
            $evenement->getDescription(),
            $evenement->getLieu(),
            $evenement->getElementsRequis(),
            $evenement->getNombrePlaces(),
            $evenement->getIdEvenement(),
        ]);
    }

    public function trouverInscription(int $idUtilisateur, int $idEvenement): ?NewInscriptionEvenement
    {
        $req = $this->pdo->prepare('SELECT * FROM inscription_evenement WHERE id_utilisateur = ? AND id_evenement = ?');
        $req->execute([$idUtilisateur, $idEvenement]);
        $ligne = $req->fetch();
        return $ligne ? new NewInscriptionEvenement($ligne) : null;
    }

    public function estOrganisateur(int $idUtilisateur, int $idEvenement): bool
    {
        $inscription = $this->trouverInscription($idUtilisateur, $idEvenement);
        return $inscription !== null && $inscription->estOrganisateur();
    }

    public function inscrire(int $idUtilisateur, NewEvenement $evenement): string
    {
        if ($this->trouverInscription($idUtilisateur, $evenement->getIdEvenement()) !== null) {
            return 'Tu es déjà inscrit(e) à cet événement.';
        }
        if ($evenement->estComplet()) {
            return 'Cet événement est complet.';
        }
        $req = $this->pdo->prepare('INSERT INTO inscription_evenement (id_utilisateur, id_evenement, organiser) VALUES (?, ?, 0)');
        $req->execute([$idUtilisateur, $evenement->getIdEvenement()]);
        return '';
    }

    public function retirerParticipant(int $idUtilisateur, int $idEvenement): void
    {
        $req = $this->pdo->prepare(
            'DELETE FROM inscription_evenement WHERE id_utilisateur = ? AND id_evenement = ? AND organiser = 0'
        );
        $req->execute([$idUtilisateur, $idEvenement]);
    }

    public function listerInscrits(int $idEvenement): array
    {
        $req = $this->pdo->prepare(
            'SELECT ie.*, u.nom, u.prenom, u.email
             FROM inscription_evenement ie
             JOIN utilisateur u ON u.id_utilisateur = ie.id_utilisateur
             WHERE ie.id_evenement = ?
             ORDER BY ie.organiser DESC, u.nom'
        );
        $req->execute([$idEvenement]);
        return $this->hydraterListe($req->fetchAll(), NewInscriptionEvenement::class);
    }
}
