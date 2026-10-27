<?php

class NewMedecin extends NewUtilisateur
{
    private ?int $idSpecialite = null;
    private ?int $idHopital = null;
    private ?int $idEtablissement = null;
    private ?string $specialite = null;
    private ?string $hopital = null;
    private ?string $nomEtablissement = null;

    public function getRole(): string { return 'medecin'; }
    public function getLibelleRole(): string { return 'Médecin'; }

    public function getIdSpecialite(): ?int { return $this->idSpecialite; }
    public function getIdHopital(): ?int { return $this->idHopital; }
    public function getIdEtablissement(): ?int { return $this->idEtablissement; }
    public function getSpecialite(): ?string { return $this->specialite; }
    public function getHopital(): ?string { return $this->hopital; }
    public function getNomEtablissement(): ?string { return $this->nomEtablissement; }

    public function setIdSpecialite(?int $id): void { $this->idSpecialite = $id; }
    public function setIdHopital(?int $id): void { $this->idHopital = $id; }
    public function setIdEtablissement(?int $id): void { $this->idEtablissement = $id; }
    public function setSpecialite(?string $libelle): void { $this->specialite = $libelle; }
    public function setHopital(?string $nom): void { $this->hopital = $nom; }
    public function setNomEtablissement(?string $nom): void { $this->nomEtablissement = $nom; }
}
