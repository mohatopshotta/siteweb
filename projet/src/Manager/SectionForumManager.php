<?php

class SectionForumManager extends Manager
{
    public function listerAvecNbPosts(): array
    {
        $lignes = $this->pdo->query(
            'SELECT s.id_section, s.nom, COUNT(p.id_post) AS nb_posts
             FROM section_forum s
             LEFT JOIN post p ON p.id_section = s.id_section
             GROUP BY s.id_section, s.nom
             ORDER BY s.id_section'
        )->fetchAll();
        return $this->hydraterListe($lignes, SectionForum::class);
    }

    public function trouverParId(int $idSection): ?SectionForum
    {
        $req = $this->pdo->prepare('SELECT * FROM section_forum WHERE id_section = ?');
        $req->execute([$idSection]);
        $ligne = $req->fetch();
        return $ligne ? new SectionForum($ligne) : null;
    }
}
