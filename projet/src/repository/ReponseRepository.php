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
             ORDER BY r.date_heure, r.id_reponse'
        );
        $req->execute([$idPost]);
        return $this->hydraterListe($req->fetchAll(), NewReponse::class);
    }

    public function trouverParId(int $idReponse): ?NewReponse
    {
        $req = $this->pdo->prepare(
            'SELECT r.*, u.nom, u.prenom
             FROM reponse r
             JOIN utilisateur u ON u.id_utilisateur = r.id_auteur
             WHERE r.id_reponse = ?'
        );
        $req->execute([$idReponse]);
        $ligne = $req->fetch();
        return $ligne ? new NewReponse($ligne) : null;
    }

    /**
     * Modifie une réponse. Seul l'auteur peut le faire (vérifié aussi dans le WHERE).
     */
    public function modifier(int $idReponse, int $idAuteur, string $contenu): bool
    {
        $req = $this->pdo->prepare('UPDATE reponse SET contenu = ? WHERE id_reponse = ? AND id_auteur = ?');
        $req->execute([$contenu, $idReponse, $idAuteur]);
        return $req->rowCount() > 0;
    }

    /**
     * Supprime une réponse. Le contrôle des droits (auteur ou gestionnaire) est fait avant l'appel.
     */
    public function supprimer(int $idReponse): bool
    {
        $req = $this->pdo->prepare('DELETE FROM reponse WHERE id_reponse = ?');
        $req->execute([$idReponse]);
        return $req->rowCount() > 0;
    }

    public function creer(NewReponse $reponse): void
    {
        $req = $this->pdo->prepare('INSERT INTO reponse (id_post, id_auteur, contenu) VALUES (?, ?, ?)');
        $req->execute([$reponse->getIdPost(), $reponse->getIdAuteur(), $reponse->getContenu()]);
    }
}
