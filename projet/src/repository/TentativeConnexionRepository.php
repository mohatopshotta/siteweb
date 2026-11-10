<?php

// Requêtes SQL de la limite de tentatives de connexion (table tentative_connexion)
// On compte les échecs par email, sur les 15 dernières minutes.
class TentativeConnexionRepository extends Repository
{
    public const MAX_ECHECS = 5;
    public const FENETRE_MINUTES = 15;

    // Une même adresse peut s'écrire en majuscules ou en minuscules : on uniformise
    private function normaliser(string $email): string
    {
        return mb_strtolower(trim($email));
    }

    public function enregistrerEchec(string $email): void
    {
        // On supprime au passage les tentatives de plus d'un jour (on garde peu de données)
        $req = $this->pdo->prepare('DELETE FROM tentative_connexion WHERE date_tentative < ?');
        $req->execute([date('Y-m-d H:i:s', time() - 86400)]);

        $req = $this->pdo->prepare('INSERT INTO tentative_connexion (email, date_tentative) VALUES (?, ?)');
        $req->execute([$this->normaliser($email), date('Y-m-d H:i:s')]);
    }

    public function tropDEchecs(string $email): bool
    {
        $req = $this->pdo->prepare('SELECT COUNT(*) FROM tentative_connexion WHERE email = ? AND date_tentative > ?');
        $req->execute([
            $this->normaliser($email),
            date('Y-m-d H:i:s', time() - self::FENETRE_MINUTES * 60),
        ]);

        return (int)$req->fetchColumn() >= self::MAX_ECHECS;
    }

    // Appelé quand la connexion réussit : le compteur repart à zéro
    public function effacer(string $email): void
    {
        $req = $this->pdo->prepare('DELETE FROM tentative_connexion WHERE email = ?');
        $req->execute([$this->normaliser($email)]);
    }
}
