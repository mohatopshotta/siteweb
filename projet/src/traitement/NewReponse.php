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
