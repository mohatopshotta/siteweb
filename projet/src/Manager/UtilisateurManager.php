<?php

/**
 * Requêtes sur les utilisateurs (tables utilisateur, etudiant, medecin, partenaire, gestionnaire).
 *
 * La table utilisateur n'a pas de colonne "role" : le rôle se déduit de la table fille
 * dans laquelle l'utilisateur a une ligne. Le manager renvoie donc directement
 * un objet Etudiant, Medecin, Partenaire ou Gestionnaire.
 */
class UtilisateurManager extends Manager
{
    // SELECT commun : infos de l'utilisateur + son rôle calculé avec des LEFT JOIN
    private const SELECT_AVEC_ROLE = "
        SELECT u.*,
               CASE
                   WHEN e.id_utilisateur IS NOT NULL THEN 'etudiant'
                   WHEN m.id_utilisateur IS NOT NULL THEN 'medecin'
                   WHEN p.id_utilisateur IS NOT NULL THEN 'partenaire'
                   WHEN g.id_utilisateur IS NOT NULL THEN 'gestionnaire'
               END AS role
        FROM utilisateur u
        LEFT JOIN etudiant e     ON e.id_utilisateur = u.id_utilisateur
        LEFT JOIN medecin m      ON m.id_utilisateur = u.id_utilisateur
        LEFT JOIN partenaire p   ON p.id_utilisateur = u.id_utilisateur
        LEFT JOIN gestionnaire g ON g.id_utilisateur = u.id_utilisateur
    ";

    /** Crée le bon objet (Etudiant, Medecin...) selon le rôle. */
    private function creerObjet(array $ligne): ?Utilisateur
    {
        return match ($ligne['role'] ?? null) {
            'etudiant'     => new Etudiant($ligne),
            'medecin'      => new Medecin($ligne),
            'partenaire'   => new Partenaire($ligne),
            'gestionnaire' => new Gestionnaire($ligne),
            default        => null,
        };
    }

    // ============================================================
    // Lecture
    // ============================================================

    public function trouverParEmail(string $email): ?Utilisateur
    {
        $req = $this->pdo->prepare(self::SELECT_AVEC_ROLE . ' WHERE u.email = ?');
        $req->execute([$email]);
        $ligne = $req->fetch();
        return $ligne ? $this->creerObjet($ligne) : null;
    }

    public function emailExiste(string $email): bool
    {
        $req = $this->pdo->prepare('SELECT 1 FROM utilisateur WHERE email = ?');
        $req->execute([$email]);
        return (bool)$req->fetchColumn();
    }

    /**
     * Utilisateur complet avec les infos de son rôle
     * (formation, spécialité, hôpital, entreprise...).
     */
    public function trouverParId(int $idUtilisateur): ?Utilisateur
    {
        $req = $this->pdo->prepare(self::SELECT_AVEC_ROLE . ' WHERE u.id_utilisateur = ?');
        $req->execute([$idUtilisateur]);
        $ligne = $req->fetch();
        if (!$ligne) {
            return null;
        }

        // infos spécifiques au rôle
        $sqlDetails = match ($ligne['role']) {
            'etudiant' => '
                SELECT e.id_etablissement, e.formation, e.cv, et.nom AS nom_etablissement
                FROM etudiant e JOIN etablissement et ON et.id_etablissement = e.id_etablissement
                WHERE e.id_utilisateur = ?',
            'medecin' => '
                SELECT m.id_specialite, m.id_hopital, m.id_etablissement,
                       s.libelle AS specialite, h.nom AS hopital, et.nom AS nom_etablissement
                FROM medecin m
                JOIN specialite s ON s.id_specialite = m.id_specialite
                JOIN hopital h ON h.id_hopital = m.id_hopital
                LEFT JOIN etablissement et ON et.id_etablissement = m.id_etablissement
                WHERE m.id_utilisateur = ?',
            'partenaire' => '
                SELECT p.id_entreprise, p.poste, en.nom AS nom_entreprise
                FROM partenaire p JOIN entreprise en ON en.id_entreprise = p.id_entreprise
                WHERE p.id_utilisateur = ?',
            default => null,
        };

        if ($sqlDetails !== null) {
            $req = $this->pdo->prepare($sqlDetails);
            $req->execute([$idUtilisateur]);
            $ligne = array_merge($ligne, $req->fetch() ?: []);
        }

        return $this->creerObjet($ligne);
    }

    /** @return Utilisateur[] comptes pas encore validés par un gestionnaire */
    public function listerEnAttente(): array
    {
        $lignes = $this->pdo->query(self::SELECT_AVEC_ROLE . ' WHERE u.valide = 0 ORDER BY u.id_utilisateur DESC')->fetchAll();
        $comptes = [];
        foreach ($lignes as $ligne) {
            $compte = $this->creerObjet($ligne);
            if ($compte !== null) {
                $comptes[] = $compte;
            }
        }
        return $comptes;
    }

    // ============================================================
    // Statistiques (tableau de bord admin)
    // ============================================================

    public function compterValides(): int
    {
        return (int)$this->pdo->query('SELECT COUNT(*) FROM utilisateur WHERE valide = 1')->fetchColumn();
    }

    public function compterEnAttente(): int
    {
        return (int)$this->pdo->query('SELECT COUNT(*) FROM utilisateur WHERE valide = 0')->fetchColumn();
    }

    /** @param string $role 'etudiant', 'medecin' ou 'partenaire' */
    public function compterParRole(string $role): int
    {
        if (!in_array($role, ['etudiant', 'medecin', 'partenaire', 'gestionnaire'], true)) {
            return 0;
        }
        // le nom de table vient de la liste ci-dessus : pas d'injection possible
        return (int)$this->pdo->query("SELECT COUNT(*) FROM $role")->fetchColumn();
    }

    // ============================================================
    // Écriture
    // ============================================================

    /**
     * Inscription d'un nouvel utilisateur (compte non validé).
     * $nouvelEtablissement / $nouvelleEntreprise : à créer si l'utilisateur a choisi "+ Nouveau".
     * Tout est fait dans une transaction : si une insertion échoue, rien n'est enregistré.
     *
     * @return int id du nouvel utilisateur
     */
    public function inscrire(
        Utilisateur $utilisateur,
        string $motDePasse,
        ?Etablissement $nouvelEtablissement = null,
        ?Entreprise $nouvelleEntreprise = null
    ): int {
        $this->pdo->beginTransaction();
        try {
            $req = $this->pdo->prepare('INSERT INTO utilisateur (nom, prenom, email, mot_de_passe) VALUES (?, ?, ?, ?)');
            $req->execute([
                $utilisateur->getNom(),
                $utilisateur->getPrenom(),
                $utilisateur->getEmail(),
                password_hash($motDePasse, PASSWORD_DEFAULT),
            ]);
            $id = (int)$this->pdo->lastInsertId();
            $utilisateur->setIdUtilisateur($id);

            if ($nouvelEtablissement !== null && ($utilisateur instanceof Etudiant || $utilisateur instanceof Medecin)) {
                $utilisateur->setIdEtablissement((new EtablissementManager())->trouverOuCreer($nouvelEtablissement));
            }
            if ($nouvelleEntreprise !== null && $utilisateur instanceof Partenaire) {
                $utilisateur->setIdEntreprise((new EntrepriseManager())->trouverOuCreer($nouvelleEntreprise));
            }

            // ligne dans la table fille selon le type d'objet
            if ($utilisateur instanceof Etudiant) {
                $req = $this->pdo->prepare('INSERT INTO etudiant (id_utilisateur, id_etablissement, cv, formation) VALUES (?, ?, ?, ?)');
                $req->execute([$id, $utilisateur->getIdEtablissement(), $utilisateur->getCv(), $utilisateur->getFormation()]);
            } elseif ($utilisateur instanceof Medecin) {
                $req = $this->pdo->prepare('INSERT INTO medecin (id_utilisateur, id_specialite, id_hopital, id_etablissement) VALUES (?, ?, ?, ?)');
                $req->execute([$id, $utilisateur->getIdSpecialite(), $utilisateur->getIdHopital(), $utilisateur->getIdEtablissement()]);
            } elseif ($utilisateur instanceof Partenaire) {
                $req = $this->pdo->prepare('INSERT INTO partenaire (id_utilisateur, id_entreprise, poste) VALUES (?, ?, ?)');
                $req->execute([$id, $utilisateur->getIdEntreprise(), $utilisateur->getPoste()]);
            } elseif ($utilisateur instanceof Gestionnaire) {
                $req = $this->pdo->prepare('INSERT INTO gestionnaire (id_utilisateur) VALUES (?)');
                $req->execute([$id]);
            }

            $this->pdo->commit();
            return $id;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function modifierNomPrenom(int $idUtilisateur, string $nom, string $prenom): void
    {
        $req = $this->pdo->prepare('UPDATE utilisateur SET nom = ?, prenom = ? WHERE id_utilisateur = ?');
        $req->execute([$nom, $prenom, $idUtilisateur]);
    }

    /** Validation d'un compte par un gestionnaire. */
    public function valider(int $idUtilisateur, int $idValidateur): void
    {
        $req = $this->pdo->prepare('UPDATE utilisateur SET valide = 1, id_validateur = ? WHERE id_utilisateur = ? AND valide = 0');
        $req->execute([$idValidateur, $idUtilisateur]);
    }

    /** Refus d'un compte : suppression (cascade sur etudiant/medecin/partenaire). */
    public function supprimerEnAttente(int $idUtilisateur): void
    {
        $req = $this->pdo->prepare('DELETE FROM utilisateur WHERE id_utilisateur = ? AND valide = 0');
        $req->execute([$idUtilisateur]);
    }
}
