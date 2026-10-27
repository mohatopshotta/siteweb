<?php

/** Requêtes sur les discussions du forum (table post). */
class PostManager extends Manager
{
    /** @return Post[] discussions d'une section, avec auteur et nombre de réponses */
    public function listerParSection(int $idSection): array
    {
        $req = $this->pdo->prepare(
            'SELECT p.*, u.nom, u.prenom,
                    (SELECT COUNT(*) FROM reponse r WHERE r.id_post = p.id_post) AS nb_reponses
             FROM post p
             JOIN utilisateur u ON u.id_utilisateur = p.id_auteur
             WHERE p.id_section = ?
             ORDER BY p.date_heure DESC'
        );
        $req->execute([$idSection]);
        return $this->hydraterListe($req->fetchAll(), Post::class);
    }

    public function trouverParId(int $idPost): ?Post
    {
        $req = $this->pdo->prepare(
            'SELECT p.*, s.nom AS section_nom, u.nom, u.prenom
             FROM post p
             JOIN section_forum s ON s.id_section = p.id_section
             JOIN utilisateur u ON u.id_utilisateur = p.id_auteur
             WHERE p.id_post = ?'
        );
        $req->execute([$idPost]);
        $ligne = $req->fetch();
        return $ligne ? new Post($ligne) : null;
    }

    /** @return int id de la nouvelle discussion */
    public function creer(Post $post): int
    {
        $req = $this->pdo->prepare('INSERT INTO post (id_section, id_auteur, titre, contenu) VALUES (?, ?, ?, ?)');
        $req->execute([$post->getIdSection(), $post->getIdAuteur(), $post->getTitre(), $post->getContenu()]);
        return (int)$this->pdo->lastInsertId();
    }

    /** @return Post[] discussions créées par l'utilisateur */
    public function listerParAuteur(int $idAuteur): array
    {
        $req = $this->pdo->prepare(
            'SELECT p.*, s.nom AS section_nom
             FROM post p
             JOIN section_forum s ON s.id_section = p.id_section
             WHERE p.id_auteur = ?
             ORDER BY p.date_heure DESC'
        );
        $req->execute([$idAuteur]);
        return $this->hydraterListe($req->fetchAll(), Post::class);
    }

    /** @return Post[] discussions où l'utilisateur a répondu */
    public function listerOuUtilisateurARepondu(int $idUtilisateur): array
    {
        $req = $this->pdo->prepare(
            'SELECT p.id_post, p.titre, s.nom AS section_nom, MAX(r.date_heure) AS derniere_reponse
             FROM reponse r
             JOIN post p ON p.id_post = r.id_post
             JOIN section_forum s ON s.id_section = p.id_section
             WHERE r.id_auteur = ?
             GROUP BY p.id_post, p.titre, s.nom
             ORDER BY derniere_reponse DESC'
        );
        $req->execute([$idUtilisateur]);
        return $this->hydraterListe($req->fetchAll(), Post::class);
    }
}
