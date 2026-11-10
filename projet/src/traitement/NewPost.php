<?php

class NewPost extends NewTraitement
{
    private ?int $idPost = null;
    private ?int $idSection = null;
    private ?int $idAuteur = null;
    private string $titre = '';
    private string $contenu = '';
    private ?string $dateHeure = null;
    private ?string $sectionNom = null;
    private ?string $nom = null;
    private ?string $prenom = null;
    private int $nbReponses = 0;
    private ?string $derniereReponse = null;

    public function getNomAuteur(): string
    {
        return trim($this->prenom . ' ' . $this->nom);
    }

    public function getDateFormatee(): string
    {
        return $this->dateHeure ? date('d/m/Y H:i', strtotime($this->dateHeure)) : '';
    }

    /** Date de la dernière réponse, ou de la création s'il n'y a pas encore de réponse. */
    public function getDerniereActiviteFormatee(): string
    {
        $date = $this->derniereReponse ?? $this->dateHeure;
        return $date ? date('d/m/Y H:i', strtotime($date)) : '';
    }

    // ---------- Droits ----------

    /** Seul l'auteur peut modifier son message. */
    public function peutModifier(?int $idUtilisateur): bool
    {
        return $idUtilisateur !== null && $idUtilisateur === $this->idAuteur;
    }

    /** L'auteur peut supprimer son message, le gestionnaire peut supprimer n'importe quel message (modération). */
    public function peutSupprimer(?int $idUtilisateur, ?string $role): bool
    {
        return $this->peutModifier($idUtilisateur) || $role === 'gestionnaire';
    }

    public function getIdPost(): ?int { return $this->idPost; }
    public function getIdSection(): ?int { return $this->idSection; }
    public function getIdAuteur(): ?int { return $this->idAuteur; }
    public function getTitre(): string { return $this->titre; }
    public function getContenu(): string { return $this->contenu; }
    public function getDateHeure(): ?string { return $this->dateHeure; }
    public function getSectionNom(): ?string { return $this->sectionNom; }
    public function getNbReponses(): int { return $this->nbReponses; }
    public function getDerniereReponse(): ?string { return $this->derniereReponse; }

    public function setIdPost(?int $id): void { $this->idPost = $id; }
    public function setIdSection(?int $id): void { $this->idSection = $id; }
    public function setIdAuteur(?int $id): void { $this->idAuteur = $id; }
    public function setTitre(string $titre): void { $this->titre = $titre; }
    public function setContenu(string $contenu): void { $this->contenu = $contenu; }
    public function setDateHeure(?string $date): void { $this->dateHeure = $date; }
    public function setSectionNom(?string $nom): void { $this->sectionNom = $nom; }
    public function setNom(?string $nom): void { $this->nom = $nom; }
    public function setPrenom(?string $prenom): void { $this->prenom = $prenom; }
    public function setNbReponses(int $nb): void { $this->nbReponses = $nb; }
    public function setDerniereReponse(?string $date): void { $this->derniereReponse = $date; }
}
