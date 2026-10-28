<?php

class NewEtablissement extends NewTraitement
{
    private ?int $idEtablissement = null;
    private string $nom = '';
    private string $adresse = '';
    private string $siteWeb = '';

    public function getIdEtablissement(): ?int { return $this->idEtablissement; }
    public function getNom(): string { return $this->nom; }
    public function getAdresse(): string { return $this->adresse; }
    public function getSiteWeb(): string { return $this->siteWeb; }

    public function setIdEtablissement(?int $id): void { $this->idEtablissement = $id; }
    public function setNom(string $nom): void { $this->nom = $nom; }
    public function setAdresse(string $adresse): void { $this->adresse = $adresse; }
    public function setSiteWeb(string $siteWeb): void { $this->siteWeb = $siteWeb; }
}
