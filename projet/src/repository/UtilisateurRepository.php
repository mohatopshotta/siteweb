<?php

class UtilisateurRepository extends Repository
{
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

    private function creerObjet(array $ligne): ?NewUtilisateur
    {
        return match ($ligne['role'] ?? null) {
            'etudiant'     => new NewEtudiant($ligne),
            'medecin'      => new NewMedecin($ligne),
            'partenaire'   => new NewPartenaire($ligne),
            'gestionnaire' => new NewGestionnaire($ligne),
            default        => null,
        };
    }

    public function trouverParEmail(string $email): ?NewUtilisateur
    {
        $req = $this->pdo->prepare(self::SELECT_AVEC_ROLE . ' WHERE u.email = ?');
        $req->execute([$email]);
        $ligne = $req->fetch();
        return $ligne ? $this->creerObjet($ligne) : null;
    }

    // Sert à supprimer plus tard les comptes inactifs depuis 3 ans (RGPD)
    public function majDerniereConnexion(int $idUtilisateur): void
    {
        $req = $this->pdo->prepare('UPDATE utilisateur SET date_derniere_connexion = NOW() WHERE id_utilisateur = ?');
        $req->execute([$idUtilisateur]);
    }

    public function emailExiste(string $email): bool
    {
        $req = $this->pdo->prepare('SELECT 1 FROM utilisateur WHERE email = ?');
        $req->execute([$email]);
        return (bool)$req->fetchColumn();
    }

    public function trouverParId(int $idUtilisateur): ?NewUtilisateur
    {
        $req = $this->pdo->prepare(self::SELECT_AVEC_ROLE . ' WHERE u.id_utilisateur = ?');
        $req->execute([$idUtilisateur]);
        $ligne = $req->fetch();
        if (!$ligne) {
            return null;
        }

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

    public function compterValides(): int
    {
        return (int)$this->pdo->query('SELECT COUNT(*) FROM utilisateur WHERE valide = 1')->fetchColumn();
    }

    public function compterEnAttente(): int
    {
        return (int)$this->pdo->query('SELECT COUNT(*) FROM utilisateur WHERE valide = 0')->fetchColumn();
    }

    public function compterParRole(string $role): int
    {
        if (!in_array($role, ['etudiant', 'medecin', 'partenaire', 'gestionnaire'], true)) {
            return 0;
        }

        return (int)$this->pdo->query("SELECT COUNT(*) FROM $role")->fetchColumn();
    }

    public function inscrire(
        NewUtilisateur $utilisateur,
        string         $motDePasse,
        ?NewEtablissement $nouvelEtablissement = null,
        ?NewEntreprise $nouvelleEntreprise = null
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

            if ($nouvelEtablissement !== null && ($utilisateur instanceof NewEtudiant || $utilisateur instanceof NewMedecin)) {
                $utilisateur->setIdEtablissement((new EtablissementRepository())->trouverOuCreer($nouvelEtablissement));
            }
            if ($nouvelleEntreprise !== null && $utilisateur instanceof NewPartenaire) {
                $utilisateur->setIdEntreprise((new EntrepriseRepository())->trouverOuCreer($nouvelleEntreprise));
            }

            if ($utilisateur instanceof NewEtudiant) {
                $req = $this->pdo->prepare('INSERT INTO etudiant (id_utilisateur, id_etablissement, cv, formation) VALUES (?, ?, ?, ?)');
                $req->execute([$id, $utilisateur->getIdEtablissement(), $utilisateur->getCv(), $utilisateur->getFormation()]);
            } elseif ($utilisateur instanceof NewMedecin) {
                $req = $this->pdo->prepare('INSERT INTO medecin (id_utilisateur, id_specialite, id_hopital, id_etablissement) VALUES (?, ?, ?, ?)');
                $req->execute([$id, $utilisateur->getIdSpecialite(), $utilisateur->getIdHopital(), $utilisateur->getIdEtablissement()]);
            } elseif ($utilisateur instanceof NewPartenaire) {
                $req = $this->pdo->prepare('INSERT INTO partenaire (id_utilisateur, id_entreprise, poste) VALUES (?, ?, ?)');
                $req->execute([$id, $utilisateur->getIdEntreprise(), $utilisateur->getPoste()]);
            } elseif ($utilisateur instanceof NewGestionnaire) {
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

    public function valider(int $idUtilisateur, int $idValidateur): void
    {
        $req = $this->pdo->prepare('UPDATE utilisateur SET valide = 1, id_validateur = ? WHERE id_utilisateur = ? AND valide = 0');
        $req->execute([$idValidateur, $idUtilisateur]);
    }

    public function supprimerEnAttente(int $idUtilisateur): void
    {
        $req = $this->pdo->prepare('DELETE FROM utilisateur WHERE id_utilisateur = ? AND valide = 0');
        $req->execute([$idUtilisateur]);
    }
}
