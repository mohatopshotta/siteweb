<?php

class CandidatureRepository extends Repository
{
    public function aDejaPostule(int $idUtilisateur, int $idOffre): bool
    {
        $req = $this->pdo->prepare('SELECT 1 FROM candidature WHERE id_utilisateur = ? AND id_offre = ?');
        $req->execute([$idUtilisateur, $idOffre]);
        return (bool)$req->fetchColumn();
    }

    public function postuler(NewCandidature $candidature, NewOffre $offre): string
    {
        if (!$offre->estOuverte()) {
            return 'Cette offre est clôturée.';
        }
        if (trim($candidature->getMotivation()) === '') {
            return 'La lettre de motivation est obligatoire.';
        }
        if ($this->aDejaPostule($candidature->getIdUtilisateur(), $offre->getIdOffre())) {
            return 'Tu as déjà postulé à cette offre.';
        }

        $req = $this->pdo->prepare('INSERT INTO candidature (id_utilisateur, id_offre, motivation) VALUES (?, ?, ?)');
        $req->execute([$candidature->getIdUtilisateur(), $offre->getIdOffre(), $candidature->getMotivation()]);

        if ($offre->getAuteurEmail()) {
            Mail::envoyer(
                $offre->getAuteurEmail(),
                'Nouvelle candidature : ' . $offre->getTitre(),
                'Une nouvelle candidature a été déposée sur votre offre « ' . $offre->getTitre() . ' ».'
            );
        }
        return '';
    }

    public function listerParOffre(int $idOffre): array
    {
        $req = $this->pdo->prepare(
            'SELECT c.*, u.nom, u.prenom, u.email
             FROM candidature c
             JOIN utilisateur u ON u.id_utilisateur = c.id_utilisateur
             WHERE c.id_offre = ? AND c.statut != ?
             ORDER BY c.date_candidature DESC'
        );
        $req->execute([$idOffre, NewCandidature::NON_RETENUE]);
        return $this->hydraterListe($req->fetchAll(), NewCandidature::class);
    }

    /**
     * Candidatures envoyées par un utilisateur, avec l'offre correspondante (page « Mes candidatures »).
     * Les plus récentes en premier.
     */
    public function listerParCandidat(int $idUtilisateur): array
    {
        $req = $this->pdo->prepare(
            'SELECT c.*, o.titre AS titre_offre, o.type_offre, o.etat AS etat_offre, e.nom AS entreprise
             FROM candidature c
             JOIN offre o ON o.id_offre = c.id_offre
             LEFT JOIN entreprise e ON e.id_entreprise = o.id_entreprise
             WHERE c.id_utilisateur = ?
             ORDER BY c.date_candidature DESC'
        );
        $req->execute([$idUtilisateur]);
        return $this->hydraterListe($req->fetchAll(), NewCandidature::class);
    }

    public function retenir(NewOffre $offre, int $idCandidat): void
    {
        $req = $this->pdo->prepare(
            'UPDATE candidature SET statut = ? WHERE id_offre = ? AND id_utilisateur = ? AND statut != ?'
        );
        $req->execute([NewCandidature::RETENUE, $offre->getIdOffre(), $idCandidat, NewCandidature::RETENUE]);

        // Le candidat est prévenu par mail, comme pour un refus
        if ($req->rowCount() > 0) {
            $candidat = (new UtilisateurRepository())->trouverParId($idCandidat);
            if ($candidat !== null) {
                $message = "Bonjour " . $candidat->getPrenom() . ",\n\n"
                    . "Bonne nouvelle : votre candidature à l'offre « " . $offre->getTitre() . " » a été retenue.\n"
                    . "L'auteur de l'offre va vous recontacter.\n\n"
                    . "Vous pouvez suivre vos candidatures dans « Mes candidatures ».\n\n"
                    . "Cordialement,\n" . $offre->getEntreprise();
                Mail::envoyer($candidat->getEmail(), 'Votre candidature a été retenue', $message);
            }
        }
    }

    public function refuser(NewOffre $offre, int $idCandidat): void
    {
        $req = $this->pdo->prepare(
            'UPDATE candidature SET statut = ? WHERE id_offre = ? AND id_utilisateur = ? AND statut != ?'
        );
        $req->execute([NewCandidature::NON_RETENUE, $offre->getIdOffre(), $idCandidat, NewCandidature::NON_RETENUE]);

        if ($req->rowCount() > 0) {
            $candidat = (new UtilisateurRepository())->trouverParId($idCandidat);
            if ($candidat !== null) {
                $message = "Bonjour " . $candidat->getPrenom() . ",\n\n"
                    . "Votre candidature à l'offre « " . $offre->getTitre() . " » n'a pas été retenue.\n\n"
                    . "Cordialement,\n" . $offre->getEntreprise();
                Mail::envoyer($candidat->getEmail(), 'Votre candidature', $message);
            }
        }
    }
}
