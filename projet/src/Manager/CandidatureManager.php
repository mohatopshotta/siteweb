<?php

class CandidatureManager extends Manager
{
    public function aDejaPostule(int $idUtilisateur, int $idOffre): bool
    {
        $req = $this->pdo->prepare('SELECT 1 FROM candidature WHERE id_utilisateur = ? AND id_offre = ?');
        $req->execute([$idUtilisateur, $idOffre]);
        return (bool)$req->fetchColumn();
    }

    public function postuler(Candidature $candidature, Offre $offre): string
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
        $req->execute([$idOffre, Candidature::NON_RETENUE]);
        return $this->hydraterListe($req->fetchAll(), Candidature::class);
    }

    public function retenir(int $idOffre, int $idCandidat): void
    {
        $req = $this->pdo->prepare('UPDATE candidature SET statut = ? WHERE id_offre = ? AND id_utilisateur = ?');
        $req->execute([Candidature::RETENUE, $idOffre, $idCandidat]);
    }

    public function refuser(Offre $offre, int $idCandidat): void
    {
        $req = $this->pdo->prepare(
            'UPDATE candidature SET statut = ? WHERE id_offre = ? AND id_utilisateur = ? AND statut != ?'
        );
        $req->execute([Candidature::NON_RETENUE, $offre->getIdOffre(), $idCandidat, Candidature::NON_RETENUE]);

        if ($req->rowCount() > 0) {
            $candidat = (new UtilisateurManager())->trouverParId($idCandidat);
            if ($candidat !== null) {
                $message = "Bonjour " . $candidat->getPrenom() . ",\n\n"
                    . "Votre candidature à l'offre « " . $offre->getTitre() . " » n'a pas été retenue.\n\n"
                    . "Cordialement,\n" . $offre->getEntreprise();
                Mail::envoyer($candidat->getEmail(), 'Votre candidature', $message);
            }
        }
    }
}
