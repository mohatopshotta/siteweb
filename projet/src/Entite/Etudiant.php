<?php

class Etudiant extends Utilisateur
{
    private ?int $idEtablissement = null;
    private ?string $cv = null;
    private string $formation = '';
    private ?string $nomEtablissement = null;

    public function getRole(): string { return 'etudiant'; }
    public function getLibelleRole(): string { return 'Étudiant'; }

    public function getIdEtablissement(): ?int { return $this->idEtablissement; }
    public function getCv(): ?string { return $this->cv; }
    public function getFormation(): string { return $this->formation; }
    public function getNomEtablissement(): ?string { return $this->nomEtablissement; }

    public function setIdEtablissement(?int $id): void { $this->idEtablissement = $id; }
    public function setCv(?string $cv): void { $this->cv = $cv; }
    public function setFormation(string $formation): void { $this->formation = $formation; }
    public function setNomEtablissement(?string $nom): void { $this->nomEtablissement = $nom; }
}
