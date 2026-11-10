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
            // date_consentement = NOW() : la page d'inscription refuse l'envoi si la case RGPD n'est pas cochée,
            // donc arriver ici veut dire que la politique de confidentialité vient d'être acceptée
            $req = $this->pdo->prepare('INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, date_consentement) VALUES (?, ?, ?, ?, NOW())');
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

    /**
     * Annuaire des médecins (comptes validés), filtrable par nom et par spécialité.
     */
    public function rechercherMedecins(string $mot = '', ?int $idSpecialite = null): array
    {
        $sql = 'SELECT u.*, m.id_specialite, m.id_hopital, m.id_etablissement,
                       s.libelle AS specialite, h.nom AS hopital, et.nom AS nom_etablissement
                FROM utilisateur u
                JOIN medecin m ON m.id_utilisateur = u.id_utilisateur
                JOIN specialite s ON s.id_specialite = m.id_specialite
                JOIN hopital h ON h.id_hopital = m.id_hopital
                LEFT JOIN etablissement et ON et.id_etablissement = m.id_etablissement
                WHERE u.valide = 1';
        $params = [];
        if (trim($mot) !== '') {
            $sql .= ' AND (u.nom LIKE ? OR u.prenom LIKE ?)';
            $like = '%' . trim($mot) . '%';
            array_push($params, $like, $like);
        }
        if ($idSpecialite !== null && $idSpecialite > 0) {
            $sql .= ' AND m.id_specialite = ?';
            $params[] = $idSpecialite;
        }
        $sql .= ' ORDER BY u.nom, u.prenom';

        $req = $this->pdo->prepare($sql);
        $req->execute($params);
        return $this->hydraterListe($req->fetchAll(), NewMedecin::class);
    }

    /**
     * Annuaire des étudiants (comptes validés), filtrable par nom ou formation.
     */
    public function rechercherEtudiants(string $mot = ''): array
    {
        $sql = 'SELECT u.*, e.id_etablissement, e.formation, e.cv, et.nom AS nom_etablissement
                FROM utilisateur u
                JOIN etudiant e ON e.id_utilisateur = u.id_utilisateur
                JOIN etablissement et ON et.id_etablissement = e.id_etablissement
                WHERE u.valide = 1';
        $params = [];
        if (trim($mot) !== '') {
            $sql .= ' AND (u.nom LIKE ? OR u.prenom LIKE ? OR e.formation LIKE ?)';
            $like = '%' . trim($mot) . '%';
            array_push($params, $like, $like, $like);
        }
        $sql .= ' ORDER BY u.nom, u.prenom';

        $req = $this->pdo->prepare($sql);
        $req->execute($params);
        return $this->hydraterListe($req->fetchAll(), NewEtudiant::class);
    }

    /**
     * Crée un compte gestionnaire (seul un autre gestionnaire peut le faire) : il est validé tout de suite.
     * Renvoie '' si tout va bien, sinon le message d'erreur.
     */
    public function creerGestionnaire(string $nom, string $prenom, string $email, string $motDePasse, int $idValidateur): string
    {
        if ($nom === '' || $prenom === '' || $email === '' || $motDePasse === '') {
            return 'Tous les champs sont obligatoires.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'Email invalide.';
        }
        $erreurs = NewUtilisateur::controlerRobustesse($motDePasse);
        if (!empty($erreurs)) {
            return implode(' ', $erreurs);
        }
        if ($this->emailExiste($email)) {
            return 'Cet email est déjà utilisé.';
        }

        $this->pdo->beginTransaction();
        try {
            $req = $this->pdo->prepare(
                'INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, valide, id_validateur, date_consentement)
                 VALUES (?, ?, ?, ?, 1, ?, NOW())'
            );
            $req->execute([$nom, $prenom, $email, password_hash($motDePasse, PASSWORD_DEFAULT), $idValidateur]);
            $id = (int)$this->pdo->lastInsertId();
            $req = $this->pdo->prepare('INSERT INTO gestionnaire (id_utilisateur) VALUES (?)');
            $req->execute([$id]);
            $this->pdo->commit();
            return '';
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log('Création gestionnaire : ' . $e->getMessage());
            return "Le gestionnaire n'a pas pu être créé.";
        }
    }

    /** Emails des gestionnaires (pour leur transmettre les messages de contact). */
    public function listerEmailsGestionnaires(): array
    {
        return $this->pdo->query(
            'SELECT u.email FROM utilisateur u JOIN gestionnaire g ON g.id_utilisateur = u.id_utilisateur'
        )->fetchAll(PDO::FETCH_COLUMN);
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

    // ---------------------------------------------------------------
    // RGPD : droit à l'effacement et durée de conservation
    // ---------------------------------------------------------------

    // Condition SQL commune : comptes sans connexion depuis X ans (ou jamais connectés depuis l'inscription)
    private const CONDITION_INACTIF = 'COALESCE(u.date_derniere_connexion, u.date_inscription) < NOW() - INTERVAL ? YEAR';

    /**
     * Supprime un compte et toutes ses données (droit à l'effacement).
     * Les lignes liées (etudiant, candidatures, posts, inscriptions...) partent grâce aux ON DELETE CASCADE.
     * Un gestionnaire ne peut pas être supprimé par cette méthode.
     * Renvoie false si rien n'a été supprimé.
     */
    public function supprimer(int $idUtilisateur): bool
    {
        // On récupère le CV avant la suppression : après, la ligne etudiant n'existe plus
        $req = $this->pdo->prepare('SELECT cv FROM etudiant WHERE id_utilisateur = ?');
        $req->execute([$idUtilisateur]);
        $cv = $req->fetchColumn();

        $req = $this->pdo->prepare('
            DELETE FROM utilisateur
            WHERE id_utilisateur = ?
              AND id_utilisateur NOT IN (SELECT id_utilisateur FROM gestionnaire)
        ');
        $req->execute([$idUtilisateur]);

        if ($req->rowCount() === 0) {
            return false;
        }

        $this->supprimerFichierCv($cv ?: null);
        return true;
    }

    /**
     * Nombre de comptes inactifs depuis $annees ans (gestionnaires exclus).
     */
    public function compterInactifs(int $annees): int
    {
        $req = $this->pdo->prepare('
            SELECT COUNT(*) FROM utilisateur u
            WHERE ' . self::CONDITION_INACTIF . '
              AND u.id_utilisateur NOT IN (SELECT id_utilisateur FROM gestionnaire)
        ');
        $req->execute([$annees]);
        return (int)$req->fetchColumn();
    }

    /**
     * Supprime les comptes inactifs depuis $annees ans (gestionnaires exclus), avec leurs CV.
     * Renvoie le nombre de comptes supprimés.
     */
    public function supprimerInactifs(int $annees): int
    {
        $req = $this->pdo->prepare('
            SELECT u.id_utilisateur FROM utilisateur u
            WHERE ' . self::CONDITION_INACTIF . '
              AND u.id_utilisateur NOT IN (SELECT id_utilisateur FROM gestionnaire)
        ');
        $req->execute([$annees]);

        $nbSupprimes = 0;
        foreach ($req->fetchAll(PDO::FETCH_COLUMN) as $id) {
            if ($this->supprimer((int)$id)) {
                $nbSupprimes++;
            }
        }
        return $nbSupprimes;
    }

    /**
     * Supprime le fichier PDF du CV dans public/uploads/cv/.
     * basename() empêche de sortir du dossier, même si le chemin en base était modifié.
     */
    private function supprimerFichierCv(?string $cv): void
    {
        if ($cv === null || $cv === '') {
            return;
        }
        $fichier = __DIR__ . '/../../public/uploads/cv/' . basename($cv);
        if (is_file($fichier)) {
            unlink($fichier);
        }
    }
}
