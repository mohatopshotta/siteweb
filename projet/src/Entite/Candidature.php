<?php

class Candidature extends Entite
{
    public const EN_ATTENTE = 'en_attente';
    public const RETENUE = 'retenue';
    public const NON_RETENUE = 'non_retenue';

    private ?int $idUtilisateur = null;
    private ?int $idOffre = null;
    private string $motivation = '';
    private ?string $dateCandidature = null;
    private string $statut = self::EN_ATTENTE;
    private ?string $nom = null;
    private ?string $prenom = null;
    private ?string $email = null;

    public function getNomCandidat(): string
    {
        return trim($this->prenom . ' ' . $this->nom);
    }

    public function getLibelleStatut(): string
    {
        return str_replace('_', ' ', $this->statut);
    }

    public function getIdUtilisateur(): ?int { return $this->idUtilisateur; }
    public function getIdOffre(): ?int { return $this->idOffre; }
    public function getMotivation(): string { return $this->motivation; }
    public function getDateCandidature(): ?string { return $this->dateCandidature; }
    public function getStatut(): string { return $this->statut; }
    public function getEmail(): ?string { return $this->email; }

    public function setIdUtilisateur(?int $id): void { $this->idUtilisateur = $id; }
    public function setIdOffre(?int $id): void { $this->idOffre = $id; }
    public function setMotivation(string $motivation): void { $this->motivation = $motivation; }
    public function setDateCandidature(?string $date): void { $this->dateCandidature = $date; }
    public function setStatut(string $statut): void { $this->statut = $statut; }
    public function setNom(?string $nom): void { $this->nom = $nom; }
    public function setPrenom(?string $prenom): void { $this->prenom = $prenom; }
    public function setEmail(?string $email): void { $this->email = $email; }
}
