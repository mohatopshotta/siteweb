<?php
// ============================================================
// src/modele/FichierCv.php
// Vérifie et enregistre un CV envoyé par formulaire (PDF, 2 Mo maximum).
// Mêmes règles qu'à l'inscription et sur la page « Mon profil ».
// Utilisé quand un étudiant joint son CV en postulant à une offre.
// ============================================================

class FichierCv
{
    public const TAILLE_MAX = 2 * 1024 * 1024; // 2 Mo

    /**
     * Vérifie le fichier envoyé et l'enregistre dans public/uploads/cv/.
     * Renvoie le chemin à mettre en base (ex : uploads/cv/3f9a....pdf),
     * ou null en cas de problème : le message d'erreur est alors mis dans $erreur.
     */
    public static function enregistrer(array $fichier, string &$erreur): ?string
    {
        if ($fichier['error'] !== UPLOAD_ERR_OK) {
            $erreur = "Erreur lors de l'envoi du CV.";
            return null;
        }
        if (strtolower(pathinfo($fichier['name'], PATHINFO_EXTENSION)) !== 'pdf'
            || mime_content_type($fichier['tmp_name']) !== 'application/pdf') {
            $erreur = 'Le CV doit être un fichier PDF.';
            return null;
        }
        if ($fichier['size'] > self::TAILLE_MAX) {
            $erreur = 'Le CV ne doit pas dépasser 2 Mo.';
            return null;
        }

        $dossier = __DIR__ . '/../../public/uploads/cv/';
        if (!is_dir($dossier)) {
            mkdir($dossier, 0755, true);
        }

        // Nom aléatoire : on ne peut pas deviner l'adresse du CV d'un autre étudiant
        $nomFichier = bin2hex(random_bytes(16)) . '.pdf';
        if (!move_uploaded_file($fichier['tmp_name'], $dossier . $nomFichier)) {
            $erreur = "Le CV n'a pas pu être enregistré. Réessaie.";
            return null;
        }

        return 'uploads/cv/' . $nomFichier;
    }

    /**
     * Vrai si un fichier a été choisi dans le champ (le champ CV est facultatif).
     */
    public static function estEnvoye(?array $fichier): bool
    {
        return $fichier !== null && ($fichier['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
    }
}
