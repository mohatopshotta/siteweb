<?php

/** Requêtes sur les réponses du forum (table reponse). */
class ReponseManager extends Manager
{
    /** @return Reponse[] réponses d'une discussion, de la plus ancienne à la plus récente */
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
        return $this->hydraterListe($req->fetchAll(), Reponse::class);
    }

    public function creer(Reponse $reponse): void
    {
        $req = $this->pdo->prepare('INSERT INTO reponse (id_post, id_auteur, contenu) VALUES (?, ?, ?)');
        $req->execute([$reponse->getIdPost(), $reponse->getIdAuteur(), $reponse->getContenu()]);
    }
}
