<?php

/**
 * Utilisateur du site (table utilisateur).
 *
 * Classe abstraite : un utilisateur est toujours un Etudiant, un Medecin,
 * un Partenaire ou un Gestionnaire (héritage, une table fille par rôle).
 * Chaque classe fille indique son rôle avec getRole().
 */
abstract class Utilisateur extends Entite
{
    private ?int $idUtilisateur = null;
    private string $nom = '';
    private string $prenom = '';
    private string $email = '';
    private string $motDePasse = '';
    private bool $valide = false;
    private ?int $idValidateur = null;
    private ?string $dateInscription = null;

    /** 'etudiant', 'medecin', 'partenaire' ou 'gestionnaire' */
    abstract public function getRole(): string;

    /** Rôle lisible pour l'affichage : 'Étudiant', 'Médecin', ... */
    abstract public function getLibelleRole(): string;

    public function getNomComplet(): string
    {
        return $this->prenom . ' ' . $this->nom;
    }

    public function verifierMotDePasse(string $motDePasse): bool
    {
        return password_verify($motDePasse, $this->motDePasse);
    }

    // ---------- getters ----------
    public function getIdUtilisateur(): ?int { return $this->idUtilisateur; }
    public function getNom(): string { return $this->nom; }
    public function getPrenom(): string { return $this->prenom; }
    public function getEmail(): string { return $this->email; }
    public function getMotDePasse(): string { return $this->motDePasse; }
    public function isValide(): bool { return $this->valide; }
    public function getIdValidateur(): ?int { return $this->idValidateur; }
    public function getDateInscription(): ?string { return $this->dateInscription; }

    // ---------- setters ----------
    public function setIdUtilisateur(?int $id): void { $this->idUtilisateur = $id; }
    public function setNom(string $nom): void { $this->nom = $nom; }
    public function setPrenom(string $prenom): void { $this->prenom = $prenom; }
    public function setEmail(string $email): void { $this->email = $email; }
    /** reçoit le mot de passe déjà haché (colonne mot_de_passe) */
    public function setMotDePasse(string $hash): void { $this->motDePasse = $hash; }
    public function setValide(bool $valide): void { $this->valide = $valide; }
    public function setIdValidateur(?int $id): void { $this->idValidateur = $id; }
    public function setDateInscription(?string $date): void { $this->dateInscription = $date; }
}
