<?php

/** Inscription d'un utilisateur à un événement, comme participant ou organisateur (table inscription_evenement). */
class InscriptionEvenement extends Entite
{
    private ?int $idUtilisateur = null;
    private ?int $idEvenement = null;
    private ?string $dateInscription = null;
    private bool $organiser = false;
    // remplis par jointure avec utilisateur
    private ?string $nom = null;
    private ?string $prenom = null;
    private ?string $email = null;

    public function estOrganisateur(): bool
    {
        return $this->organiser;
    }

    public function getNomComplet(): string
    {
        return trim($this->prenom . ' ' . $this->nom);
    }

    public function getIdUtilisateur(): ?int { return $this->idUtilisateur; }
    public function getIdEvenement(): ?int { return $this->idEvenement; }
    public function getDateInscription(): ?string { return $this->dateInscription; }
    public function getEmail(): ?string { return $this->email; }

    public function setIdUtilisateur(?int $id): void { $this->idUtilisateur = $id; }
    public function setIdEvenement(?int $id): void { $this->idEvenement = $id; }
    public function setDateInscription(?string $date): void { $this->dateInscription = $date; }
    public function setOrganiser(bool $organiser): void { $this->organiser = $organiser; }
    public function setNom(?string $nom): void { $this->nom = $nom; }
    public function setPrenom(?string $prenom): void { $this->prenom = $prenom; }
    public function setEmail(?string $email): void { $this->email = $email; }
}
