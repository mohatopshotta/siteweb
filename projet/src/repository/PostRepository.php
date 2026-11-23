<?php

class PostRepository extends Repository
{
    // Recherche : le mot est cherché dans le titre, le message et les réponses de la discussion
    private const CONDITION_RECHERCHE = '(p.titre LIKE :recherche OR p.contenu LIKE :recherche
        OR EXISTS (SELECT 1 FROM reponse r2 WHERE r2.id_post = p.id_post AND r2.contenu LIKE :recherche))';

    /**
     * Discussions d'une section, les plus actives en premier (dernière réponse, sinon date de création).
     * $recherche : filtre facultatif. $page / $parPage : pagination.
     */
    public function listerParSection(int $idSection, string $recherche = '', int $page = 1, int $parPage = 10): array
    {
        $sql = 'SELECT p.*, u.nom, u.prenom,
                       (SELECT COUNT(*) FROM reponse r WHERE r.id_post = p.id_post) AS nb_reponses,
                       (SELECT MAX(r.date_heure) FROM reponse r WHERE r.id_post = p.id_post) AS derniere_reponse
                FROM post p
                JOIN utilisateur u ON u.id_utilisateur = p.id_auteur
                WHERE p.id_section = :section';
        if ($recherche !== '') {
            $sql .= ' AND ' . self::CONDITION_RECHERCHE;
        }
        $sql .= ' ORDER BY COALESCE(derniere_reponse, p.date_heure) DESC, p.id_post DESC
                  LIMIT :limite OFFSET :decalage';

        $req = $this->pdo->prepare($sql);
        $req->bindValue(':section', $idSection, PDO::PARAM_INT);
        if ($recherche !== '') {
            $req->bindValue(':recherche', '%' . $this->echapperLike($recherche) . '%');
        }
        // LIMIT et OFFSET doivent être des entiers, d'où PARAM_INT
        $req->bindValue(':limite', $parPage, PDO::PARAM_INT);
        $req->bindValue(':decalage', ($page - 1) * $parPage, PDO::PARAM_INT);
        $req->execute();
        return $this->hydraterListe($req->fetchAll(), NewPost::class);
    }

    /** Échappe % et _ : sinon ce sont des jokers SQL et « % » trouverait tout. */
    private function echapperLike(string $texte): string
    {
        return addcslashes($texte, '%_\\');
    }

    /**
     * Nombre de discussions d'une section (avec le même filtre de recherche), pour la pagination.
     */
    public function compterParSection(int $idSection, string $recherche = ''): int
    {
        $sql = 'SELECT COUNT(*) FROM post p WHERE p.id_section = :section';
        if ($recherche !== '') {
            $sql .= ' AND ' . self::CONDITION_RECHERCHE;
        }
        $req = $this->pdo->prepare($sql);
        $req->bindValue(':section', $idSection, PDO::PARAM_INT);
        if ($recherche !== '') {
            $req->bindValue(':recherche', '%' . $this->echapperLike($recherche) . '%');
        }
        $req->execute();
        return (int)$req->fetchColumn();
    }

    public function trouverParId(int $idPost): ?NewPost
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
        return $ligne ? new NewPost($ligne) : null;
    }

    public function creer(NewPost $post): int
    {
        $req = $this->pdo->prepare('INSERT INTO post (id_section, id_auteur, titre, contenu) VALUES (?, ?, ?, ?)');
        $req->execute([$post->getIdSection(), $post->getIdAuteur(), $post->getTitre(), $post->getContenu()]);
        return (int)$this->pdo->lastInsertId();
    }

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
        return $this->hydraterListe($req->fetchAll(), NewPost::class);
    }

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
        return $this->hydraterListe($req->fetchAll(), NewPost::class);
    }

    /**
     * Modifie une discussion. Seul l'auteur peut le faire (vérifié aussi dans le WHERE).
     */
    public function modifier(int $idPost, int $idAuteur, string $titre, string $contenu): bool
    {
        $req = $this->pdo->prepare('UPDATE post SET titre = ?, contenu = ? WHERE id_post = ? AND id_auteur = ?');
        $req->execute([$titre, $contenu, $idPost, $idAuteur]);
        return $req->rowCount() > 0;
    }

    /**
     * Supprime une discussion. Ses réponses partent aussi (ON DELETE CASCADE).
     * Le contrôle des droits (auteur ou gestionnaire) est fait avant l'appel.
     */
    public function supprimer(int $idPost): bool
    {
        $req = $this->pdo->prepare('DELETE FROM post WHERE id_post = ?');
        $req->execute([$idPost]);
        return $req->rowCount() > 0;
    }
}
