<?php

class ReponseRepository extends Repository
{
    public function listerParPost(int $idPost): array
    {
        $req = $this->pdo->prepare(
            'SELECT r.*, u.nom, u.prenom
             FROM reponse r
             JOIN utilisateur u ON u.id_utilisateur = r.id_auteur
             WHERE r.id_post = ?
             ORDER BY r.date_heure'
        );
        $req->execute([$idPost]);
        return $this->hydraterListe($req->fetchAll(), NewReponse::class);
    }

    public function creer(NewReponse $reponse): void
    {
        $req = $this->pdo->prepare('INSERT INTO reponse (id_post, id_auteur, contenu) VALUES (?, ?, ?)');
        $req->execute([$reponse->getIdPost(), $reponse->getIdAuteur(), $reponse->getContenu()]);
    }
}
