<?php

/** Offre de stage / alternance / CDD / CDI publiée par un partenaire (table offre). */
class Offre extends Entite
{
    public const TYPES = ['stage', 'alternance', 'cdd', 'cdi'];
    public const OUVERT = 'ouvert';
    public const CLOTURE = 'cloture';

    private ?int $idOffre = null;
    private ?int $idAuteur = null;
    private ?int $idEntreprise = null;
    private string $titre = '';
    private string $description = '';
    private string $missions = '';
    private ?float $salaire = null;
    private string $typeOffre = 'stage';
    private string $etat = self::OUVERT;
    private ?string $datePublication = null;
    // remplis par jointure
    private ?string $entreprise = null;
    private ?string $auteurNom = null;
    private ?string $auteurPrenom = null;
    private ?string $auteurEmail = null;

    public function estOuverte(): bool
    {
        return $this->etat === self::OUVERT;
    }

    public function getNomAuteur(): string
    {
        return trim($this->auteurPrenom . ' ' . $this->auteurNom);
    }

    public function getIdOffre(): ?int { return $this->idOffre; }
    public function getIdAuteur(): ?int { return $this->idAuteur; }
    public function getIdEntreprise(): ?int { return $this->idEntreprise; }
    public function getTitre(): string { return $this->titre; }
    public function getDescription(): string { return $this->description; }
    public function getMissions(): string { return $this->missions; }
    public function getSalaire(): ?float { return $this->salaire; }
    public function getTypeOffre(): string { return $this->typeOffre; }
    public function getEtat(): string { return $this->etat; }
    public function getDatePublication(): ?string { return $this->datePublication; }
    public function getEntreprise(): ?string { return $this->entreprise; }
    public function getAuteurEmail(): ?string { return $this->auteurEmail; }

    public function setIdOffre(?int $id): void { $this->idOffre = $id; }
    public function setIdAuteur(?int $id): void { $this->idAuteur = $id; }
    public function setIdEntreprise(?int $id): void { $this->idEntreprise = $id; }
    public function setTitre(string $titre): void { $this->titre = $titre; }
    public function setDescription(string $description): void { $this->description = $description; }
    public function setMissions(string $missions): void { $this->missions = $missions; }
    public function setSalaire(?float $salaire): void { $this->salaire = $salaire; }
    public function setTypeOffre(string $type): void { $this->typeOffre = $type; }
    public function setEtat(string $etat): void { $this->etat = $etat; }
    public function setDatePublication(?string $date): void { $this->datePublication = $date; }
    public function setEntreprise(?string $nom): void { $this->entreprise = $nom; }
    public function setAuteurNom(?string $nom): void { $this->auteurNom = $nom; }
    public function setAuteurPrenom(?string $prenom): void { $this->auteurPrenom = $prenom; }
    public function setAuteurEmail(?string $email): void { $this->auteurEmail = $email; }

    /**
     * Vérifie le formulaire de création d'offre.
     * @return string message d'erreur ('' si tout est bon)
     */
    public static function validerFormulaire(array $post): string
    {
        $titre = trim($post['titre'] ?? '');
        $salaire = trim($post['salaire'] ?? '');

        if ($titre === '' || trim($post['description'] ?? '') === '' || trim($post['missions'] ?? '') === '') {
            return "Le titre, la description et les missions sont obligatoires.";
        }
        if (mb_strlen($titre) > 150) {
            return "Le titre est trop long (150 caractères maximum).";
        }
        if (!in_array($post['type_offre'] ?? '', self::TYPES, true)) {
            return "Type d'offre invalide.";
        }
        if ($salaire !== '' && !is_numeric($salaire)) {
            return "Le salaire doit être un nombre.";
        }
        return '';
    }
}
