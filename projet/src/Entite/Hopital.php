<?php

class Hopital extends Entite
{
    private ?int $idHopital = null;
    private string $nom = '';
    private string $commune = '';

    public function getIdHopital(): ?int { return $this->idHopital; }
    public function getNom(): string { return $this->nom; }
    public function getCommune(): string { return $this->commune; }

    public function setIdHopital(?int $id): void { $this->idHopital = $id; }
    public function setNom(string $nom): void { $this->nom = $nom; }
    public function setCommune(string $commune): void { $this->commune = $commune; }
}
