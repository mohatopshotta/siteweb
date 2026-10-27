<?php

class Specialite extends Entite
{
    private ?int $idSpecialite = null;
    private string $libelle = '';

    public function getIdSpecialite(): ?int { return $this->idSpecialite; }
    public function getLibelle(): string { return $this->libelle; }

    public function setIdSpecialite(?int $id): void { $this->idSpecialite = $id; }
    public function setLibelle(string $libelle): void { $this->libelle = $libelle; }
}
