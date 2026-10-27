<?php

/** Entreprise partenaire (table entreprise). */
class Entreprise extends Entite
{
    private ?int $idEntreprise = null;
    private string $nom = '';
    private string $adresse = '';
    private string $siteWeb = '';

    public function getIdEntreprise(): ?int { return $this->idEntreprise; }
    public function getNom(): string { return $this->nom; }
    public function getAdresse(): string { return $this->adresse; }
    public function getSiteWeb(): string { return $this->siteWeb; }

    public function setIdEntreprise(?int $id): void { $this->idEntreprise = $id; }
    public function setNom(string $nom): void { $this->nom = $nom; }
    public function setAdresse(string $adresse): void { $this->adresse = $adresse; }
    public function setSiteWeb(string $siteWeb): void { $this->siteWeb = $siteWeb; }
}
