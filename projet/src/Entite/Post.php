<?php

/** Discussion du forum (table post). */
class Post extends Entite
{
    private ?int $idPost = null;
    private ?int $idSection = null;
    private ?int $idAuteur = null;
    private string $titre = '';
    private string $contenu = '';
    private ?string $dateHeure = null;
    // remplis par jointure
    private ?string $sectionNom = null;
    private ?string $nom = null;      // nom de l'auteur
    private ?string $prenom = null;   // prénom de l'auteur
    private int $nbReponses = 0;
    private ?string $derniereReponse = null;

    public function getNomAuteur(): string
    {
        return trim($this->prenom . ' ' . $this->nom);
    }

    /** Date au format 25/10/2026 14:30 */
    public function getDateFormatee(): string
    {
        return $this->dateHeure ? date('d/m/Y H:i', strtotime($this->dateHeure)) : '';
    }

    public function getIdPost(): ?int { return $this->idPost; }
    public function getIdSection(): ?int { return $this->idSection; }
    public function getIdAuteur(): ?int { return $this->idAuteur; }
    public function getTitre(): string { return $this->titre; }
    public function getContenu(): string { return $this->contenu; }
    public function getDateHeure(): ?string { return $this->dateHeure; }
    public function getSectionNom(): ?string { return $this->sectionNom; }
    public function getNbReponses(): int { return $this->nbReponses; }
    public function getDerniereReponse(): ?string { return $this->derniereReponse; }

    public function setIdPost(?int $id): void { $this->idPost = $id; }
    public function setIdSection(?int $id): void { $this->idSection = $id; }
    public function setIdAuteur(?int $id): void { $this->idAuteur = $id; }
    public function setTitre(string $titre): void { $this->titre = $titre; }
    public function setContenu(string $contenu): void { $this->contenu = $contenu; }
    public function setDateHeure(?string $date): void { $this->dateHeure = $date; }
    public function setSectionNom(?string $nom): void { $this->sectionNom = $nom; }
    public function setNom(?string $nom): void { $this->nom = $nom; }
    public function setPrenom(?string $prenom): void { $this->prenom = $prenom; }
    public function setNbReponses(int $nb): void { $this->nbReponses = $nb; }
    public function setDerniereReponse(?string $date): void { $this->derniereReponse = $date; }
}
