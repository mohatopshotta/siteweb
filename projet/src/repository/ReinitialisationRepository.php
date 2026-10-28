<?php

// Requêtes SQL du « mot de passe oublié » (table reinitialisation_mdp)
class ReinitialisationRepository extends Repository
{
    // Un lien de réinitialisation est valable 1 heure
    private const DUREE_VALIDITE = 3600;

    // Crée un jeton pour cet utilisateur et le renvoie EN CLAIR (pour le mettre dans le lien).
    // En base, on garde seulement son empreinte SHA-256 : si la base fuit, les liens restent inutilisables.
    public function creerJeton(int $idUtilisateur): string
    {
        $jeton = bin2hex(random_bytes(32));

        // On supprime les jetons expirés et les anciens jetons de cet utilisateur
        $req = $this->pdo->prepare('DELETE FROM reinitialisation_mdp WHERE date_expiration < ? OR id_utilisateur = ?');
        $req->execute([date('Y-m-d H:i:s'), $idUtilisateur]);

        $req = $this->pdo->prepare('INSERT INTO reinitialisation_mdp (id_utilisateur, jeton_hash, date_expiration) VALUES (?, ?, ?)');
        $req->execute([
            $idUtilisateur,
            hash('sha256', $jeton),
            date('Y-m-d H:i:s', time() + self::DUREE_VALIDITE),
        ]);

        return $jeton;
    }

    // Renvoie l'id de l'utilisateur si le jeton existe et n'est pas expiré, sinon null
    public function trouverIdUtilisateur(string $jeton): ?int
    {
        $req = $this->pdo->prepare('SELECT id_utilisateur FROM reinitialisation_mdp WHERE jeton_hash = ? AND date_expiration > ?');
        $req->execute([hash('sha256', $jeton), date('Y-m-d H:i:s')]);
        $id = $req->fetchColumn();

        return $id === false ? null : (int)$id;
    }

    // Change le mot de passe ET supprime les jetons : le lien ne marche qu'une fois
    public function changerMotDePasse(int $idUtilisateur, string $motDePasseHache): void
    {
        $this->pdo->beginTransaction();
        try {
            $req = $this->pdo->prepare('UPDATE utilisateur SET mot_de_passe = ? WHERE id_utilisateur = ?');
            $req->execute([$motDePasseHache, $idUtilisateur]);

            $req = $this->pdo->prepare('DELETE FROM reinitialisation_mdp WHERE id_utilisateur = ?');
            $req->execute([$idUtilisateur]);

            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
