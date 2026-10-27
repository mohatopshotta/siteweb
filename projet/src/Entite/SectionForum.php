<?php

class SectionForum extends Entite
{
    public const GENERALE = 1;
    public const MEDECINS_PARTENAIRES = 2;
    public const ETUDIANTS = 3;

    private ?int $idSection = null;
    private string $nom = '';
    private int $nbPosts = 0;

    public function peutLire(?string $role): bool
    {
        return match ($this->idSection) {
            self::GENERALE             => true,
            self::MEDECINS_PARTENAIRES => in_array($role, ['medecin', 'partenaire', 'gestionnaire'], true),
            self::ETUDIANTS            => in_array($role, ['etudiant', 'medecin', 'gestionnaire'], true),
            default                    => false,
        };
    }

    public function peutCreer(?string $role): bool
    {
        return match ($this->idSection) {
            self::GENERALE             => $role !== null,
            self::MEDECINS_PARTENAIRES => in_array($role, ['medecin', 'partenaire'], true),
            self::ETUDIANTS            => $role === 'etudiant',
            default                    => false,
        };
    }

    public function getDescriptionAcces(): string
    {
        return match ($this->idSection) {
            self::MEDECINS_PARTENAIRES => 'Réservée aux médecins et partenaires',
            self::ETUDIANTS            => 'Ouverte aux étudiants et médecins (création réservée aux étudiants)',
            default                    => 'Ouverte à tous les membres',
        };
    }

    public function getIdSection(): ?int { return $this->idSection; }
    public function getNom(): string { return $this->nom; }
    public function getNbPosts(): int { return $this->nbPosts; }

    public function setIdSection(?int $id): void { $this->idSection = $id; }
    public function setNom(string $nom): void { $this->nom = $nom; }
    public function setNbPosts(int $nb): void { $this->nbPosts = $nb; }
}
