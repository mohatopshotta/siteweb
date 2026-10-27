<?php

abstract class NewUtilisateur extends NewTraitement
{
    private ?int $idUtilisateur = null;
    private string $nom = '';
    private string $prenom = '';
    private string $email = '';
    private string $motDePasse = '';
    private bool $valide = false;
    private ?int $idValidateur = null;
    private ?string $dateInscription = null;

    abstract public function getRole(): string;

    abstract public function getLibelleRole(): string;

    public function getNomComplet(): string
    {
        return $this->prenom . ' ' . $this->nom;
    }

    public function verifierMotDePasse(string $motDePasse): bool
    {
        return password_verify($motDePasse, $this->motDePasse);
    }

    /**
     * Règles de la CNIL : 12 caractères minimum, avec une minuscule,
     * une majuscule, un chiffre et un caractère spécial.
     * Renvoie la liste des règles non respectées (tableau vide = mot de passe accepté).
     * Sert aussi pour le futur « mot de passe oublié ».
     */
    public static function controlerRobustesse(string $motDePasse): array
    {
        $erreurs = [];
        if (mb_strlen($motDePasse) < 12) {
            $erreurs[] = 'Le mot de passe doit contenir au moins 12 caractères.';
        }
        if (!preg_match('/[a-z]/', $motDePasse)) {
            $erreurs[] = 'Le mot de passe doit contenir au moins une minuscule.';
        }
        if (!preg_match('/[A-Z]/', $motDePasse)) {
            $erreurs[] = 'Le mot de passe doit contenir au moins une majuscule.';
        }
        if (!preg_match('/[0-9]/', $motDePasse)) {
            $erreurs[] = 'Le mot de passe doit contenir au moins un chiffre.';
        }
        if (!preg_match('/[^a-zA-Z0-9]/', $motDePasse)) {
            $erreurs[] = 'Le mot de passe doit contenir au moins un caractère spécial.';
        }
        return $erreurs;
    }

    public function getIdUtilisateur(): ?int { return $this->idUtilisateur; }
    public function getNom(): string { return $this->nom; }
    public function getPrenom(): string { return $this->prenom; }
    public function getEmail(): string { return $this->email; }
    public function getMotDePasse(): string { return $this->motDePasse; }
    public function isValide(): bool { return $this->valide; }
    public function getIdValidateur(): ?int { return $this->idValidateur; }
    public function getDateInscription(): ?string { return $this->dateInscription; }

    public function setIdUtilisateur(?int $id): void { $this->idUtilisateur = $id; }
    public function setNom(string $nom): void { $this->nom = $nom; }
    public function setPrenom(string $prenom): void { $this->prenom = $prenom; }
    public function setEmail(string $email): void { $this->email = $email; }
    public function setMotDePasse(string $hash): void { $this->motDePasse = $hash; }
    public function setValide(bool $valide): void { $this->valide = $valide; }
    public function setIdValidateur(?int $id): void { $this->idValidateur = $id; }
    public function setDateInscription(?string $date): void { $this->dateInscription = $date; }
}
