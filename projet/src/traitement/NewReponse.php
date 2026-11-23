<?php

class NewReponse extends NewTraitement
{
    private ?int $idReponse = null;
    private ?int $idPost = null;
    private ?int $idAuteur = null;
    private string $contenu = '';
    private ?string $dateHeure = null;
    private ?string $nom = null;
    private ?string $prenom = null;

    public function getNomAuteur(): string
    {
        return trim($this->prenom . ' ' . $this->nom);
    }

    public function getDateFormatee(): string
    {
        return $this->dateHeure ? date('d/m/Y H:i', strtotime($this->dateHeure)) : '';
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

    public function getIdReponse(): ?int { return $this->idReponse; }
    public function getIdPost(): ?int { return $this->idPost; }
    public function getIdAuteur(): ?int { return $this->idAuteur; }
    public function getContenu(): string { return $this->contenu; }
    public function getDateHeure(): ?string { return $this->dateHeure; }

    public function setIdReponse(?int $id): void { $this->idReponse = $id; }
    public function setIdPost(?int $id): void { $this->idPost = $id; }
    public function setIdAuteur(?int $id): void { $this->idAuteur = $id; }
    public function setContenu(string $contenu): void { $this->contenu = $contenu; }
    public function setDateHeure(?string $date): void { $this->dateHeure = $date; }
    public function setNom(?string $nom): void { $this->nom = $nom; }
    public function setPrenom(?string $prenom): void { $this->prenom = $prenom; }
}
