<?php
// ============================================================
// src/repository/DonneesPersonnellesRepository.php
// RGPD : droit d'accès et droit à la portabilité.
// Rassemble toutes les données d'un utilisateur pour qu'il puisse
// les télécharger depuis la page « Mon profil » (pages/mes_donnees.php).
// Le mot de passe n'est jamais exporté (même haché).
// ============================================================

class DonneesPersonnellesRepository extends Repository
{
    /**
     * Toutes les données de l'utilisateur, rangées par catégorie.
     * Renvoie null si le compte n'existe pas.
     */
    public function exporter(int $idUtilisateur): ?array
    {
        $compte = $this->recupererCompte($idUtilisateur);
        if ($compte === null) {
            return null;
        }

        return [
            'date_export'             => date('Y-m-d H:i:s'),
            'compte'                  => $compte,
            'profil'                  => $this->recupererProfil($idUtilisateur),
            'candidatures'            => $this->recupererCandidatures($idUtilisateur),
            'inscriptions_evenements' => $this->recupererInscriptions($idUtilisateur),
            'offres_publiees'         => $this->recupererOffres($idUtilisateur),
            'discussions_forum'       => $this->recupererPosts($idUtilisateur),
            'reponses_forum'          => $this->recupererReponses($idUtilisateur),
        ];
    }

    private function recupererCompte(int $idUtilisateur): ?array
    {
        // On choisit les colonnes une par une pour ne pas exporter le mot de passe
        $req = $this->pdo->prepare('
            SELECT nom, prenom, email, date_inscription, date_consentement, date_derniere_connexion
            FROM utilisateur
            WHERE id_utilisateur = ?
        ');
        $req->execute([$idUtilisateur]);
        $ligne = $req->fetch(PDO::FETCH_ASSOC);
        return $ligne ?: null;
    }

    /**
     * Informations liées au rôle (étudiant, médecin, partenaire ou gestionnaire).
     */
    private function recupererProfil(int $idUtilisateur): array
    {
        $requetes = [
            'Étudiant' => '
                SELECT e.formation, et.nom AS etablissement, e.cv AS fichier_cv
                FROM etudiant e
                LEFT JOIN etablissement et ON et.id_etablissement = e.id_etablissement
                WHERE e.id_utilisateur = ?',
            'Médecin' => '
                SELECT s.libelle AS specialite, h.nom AS hopital, et.nom AS etablissement
                FROM medecin m
                LEFT JOIN specialite s ON s.id_specialite = m.id_specialite
                LEFT JOIN hopital h ON h.id_hopital = m.id_hopital
                LEFT JOIN etablissement et ON et.id_etablissement = m.id_etablissement
                WHERE m.id_utilisateur = ?',
            'Partenaire' => '
                SELECT p.poste, en.nom AS entreprise
                FROM partenaire p
                LEFT JOIN entreprise en ON en.id_entreprise = p.id_entreprise
                WHERE p.id_utilisateur = ?',
            'Gestionnaire' => '
                SELECT id_utilisateur FROM gestionnaire WHERE id_utilisateur = ?',
        ];

        foreach ($requetes as $role => $sql) {
            $req = $this->pdo->prepare($sql);
            $req->execute([$idUtilisateur]);
            $ligne = $req->fetch(PDO::FETCH_ASSOC);
            if ($ligne) {
                unset($ligne['id_utilisateur']);
                return ['role' => $role] + $ligne;
            }
        }
        return [];
    }

    private function recupererCandidatures(int $idUtilisateur): array
    {
        $req = $this->pdo->prepare('
            SELECT o.titre AS offre, c.motivation, c.date_candidature, c.statut
            FROM candidature c
            JOIN offre o ON o.id_offre = c.id_offre
            WHERE c.id_utilisateur = ?
            ORDER BY c.date_candidature
        ');
        $req->execute([$idUtilisateur]);
        return $req->fetchAll(PDO::FETCH_ASSOC);
    }

    private function recupererInscriptions(int $idUtilisateur): array
    {
        $req = $this->pdo->prepare('
            SELECT e.titre AS evenement, e.date_evenement, e.lieu, i.date_inscription,
                   IF(i.organiser = 1, \'oui\', \'non\') AS organisateur
            FROM inscription_evenement i
            JOIN evenement e ON e.id_evenement = i.id_evenement
            WHERE i.id_utilisateur = ?
            ORDER BY i.date_inscription
        ');
        $req->execute([$idUtilisateur]);
        return $req->fetchAll(PDO::FETCH_ASSOC);
    }

    private function recupererOffres(int $idUtilisateur): array
    {
        $req = $this->pdo->prepare('
            SELECT titre, type_offre, etat, date_publication, description, missions, salaire
            FROM offre
            WHERE id_auteur = ?
            ORDER BY date_publication
        ');
        $req->execute([$idUtilisateur]);
        return $req->fetchAll(PDO::FETCH_ASSOC);
    }

    private function recupererPosts(int $idUtilisateur): array
    {
        $req = $this->pdo->prepare('
            SELECT s.nom AS section, p.titre, p.contenu, p.date_heure
            FROM post p
            JOIN section_forum s ON s.id_section = p.id_section
            WHERE p.id_auteur = ?
            ORDER BY p.date_heure
        ');
        $req->execute([$idUtilisateur]);
        return $req->fetchAll(PDO::FETCH_ASSOC);
    }

    private function recupererReponses(int $idUtilisateur): array
    {
        $req = $this->pdo->prepare('
            SELECT p.titre AS discussion, r.contenu, r.date_heure
            FROM reponse r
            JOIN post p ON p.id_post = r.id_post
            WHERE r.id_auteur = ?
            ORDER BY r.date_heure
        ');
        $req->execute([$idUtilisateur]);
        return $req->fetchAll(PDO::FETCH_ASSOC);
    }
}
