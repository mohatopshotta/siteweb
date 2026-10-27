<?php

class Partenaire extends Utilisateur
{
    private ?int $idEntreprise = null;
    private string $poste = '';
    private ?string $nomEntreprise = null;

    public function getRole(): string { return 'partenaire'; }
    public function getLibelleRole(): string { return 'Partenaire'; }

    public function getIdEntreprise(): ?int { return $this->idEntreprise; }
    public function getPoste(): string { return $this->poste; }
    public function getNomEntreprise(): ?string { return $this->nomEntreprise; }

    public function setIdEntreprise(?int $id): void { $this->idEntreprise = $id; }
    public function setPoste(string $poste): void { $this->poste = $poste; }
    public function setNomEntreprise(?string $nom): void { $this->nomEntreprise = $nom; }
}
