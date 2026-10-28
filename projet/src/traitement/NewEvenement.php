<?php

class NewEvenement extends NewTraitement
{
    private ?int $idEvenement = null;
    private string $typeEvenement = '';
    private string $titre = '';
    private string $description = '';
    private string $lieu = '';
    private ?string $dateEvenement = null;

    private bool $estOrganisateur = false;
    private ?string $elementsRequis = null;
    private int $nombrePlaces = 0;
    private int $placesPrises = 0;
    private bool $estOrganisateur = false;

    public function getPlacesRestantes(): int
    {
        return max(0, $this->nombrePlaces - $this->placesPrises);
    }

    public function estComplet(): bool
    {
        return $this->getPlacesRestantes() === 0;
    }
    public function estPasse(): bool
    {
        return $this->dateEvenement !== null && strtotime($this->dateEvenement) < time();
    }

    /** « 12/11/2026 à 14h30 », ou « Date à venir » si elle n'est pas fixée. */
    public function getDateAffichage(): string
    {
        if ($this->dateEvenement === null) {
            return 'Date à venir';
        }
        $t = strtotime($this->dateEvenement);
        return date('d/m/Y', $t) . ' à ' . date('H\hi', $t);
    }

    /** Valeur pour <input type="datetime-local"> */
    public function getDateFormulaire(): string
    {
        return $this->dateEvenement === null ? '' : date('Y-m-d\TH:i', strtotime($this->dateEvenement));
    }

    public function getIdEvenement(): ?int { return $this->idEvenement; }
    public function getTypeEvenement(): string { return $this->typeEvenement; }
    public function getTitre(): string { return $this->titre; }
    public function getDescription(): string { return $this->description; }
    public function getDateEvenement(): ?string { return $this->dateEvenement; }
    public function getLieu(): string { return $this->lieu; }
    public function getElementsRequis(): ?string { return $this->elementsRequis; }
    public function estOrganisateur(): bool { return $this->estOrganisateur; }
    public function getNombrePlaces(): int { return $this->nombrePlaces; }
    public function getPlacesPrises(): int { return $this->placesPrises; }

    public function setIdEvenement(?int $id): void { $this->idEvenement = $id; }
    public function setTypeEvenement(string $type): void { $this->typeEvenement = $type; }
    public function setTitre(string $titre): void { $this->titre = $titre; }
    public function setDescription(string $description): void { $this->description = $description; }
    public function setDateEvenement(?string $date): void
    {
        $this->dateEvenement = ($date === null || $date === '') ? null : str_replace('T', ' ', $date);
    }
    public function setLieu(string $lieu): void { $this->lieu = $lieu; }
    public function setElementsRequis(?string $elements): void { $this->elementsRequis = ($elements === '' ? null : $elements); }
    public function setEstOrganisateur(bool $oui): void { $this->estOrganisateur = $oui; }
    public function setNombrePlaces(int $nombre): void { $this->nombrePlaces = $nombre; }
    public function setPlacesPrises(int $nombre): void { $this->placesPrises = $nombre; }

    public static function validerFormulaire(array $post, bool $creation = false): array
    {
        $erreurs = [];
        if (trim($post['type_evenement'] ?? '') === '') $erreurs[] = "Le type d'événement est obligatoire.";
        if (trim($post['titre'] ?? '') === '') $erreurs[] = "Le titre est obligatoire.";
        if (trim($post['description'] ?? '') === '') $erreurs[] = "La description est obligatoire.";
        if (trim($post['lieu'] ?? '') === '') $erreurs[] = "Le lieu est obligatoire.";
        $places = (string)($post['nombre_places'] ?? '');
        if (!ctype_digit($places) || (int)$places < 1) {
            $erreurs[] = "Le nombre de places doit être un entier positif.";
        }
        return $erreurs;
    }

    public static function depuisFormulaire(array $post, ?int $idEvenement = null): self
    {
        return new self([
            'id_evenement'    => $idEvenement,
            'type_evenement'  => trim($post['type_evenement']),
            'titre'           => trim($post['titre']),
            'date_evenement'  => trim($post['date_evenement'] ?? ''),
            'description'     => trim($post['description']),
            'lieu'            => trim($post['lieu']),
            'elements_requis' => trim($post['elements_requis'] ?? ''),
            'nombre_places'   => (int)$post['nombre_places'],
        ]);
    }
}
