<?php
require __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/page_erreur.php';

Session::exigerConnexion('../../');

$repo = new EvenementRepository();
$id_utilisateur = Session::getIdUtilisateur();
$id_evenement = (int)($_GET['id'] ?? 0);

$evenement = $repo->trouverParId($id_evenement);
if ($evenement === null) {
    pageErreur(404, 'Événement introuvable', "Cet événement n'existe pas ou a été supprimé.");
}

if (!$repo->estOrganisateur($id_utilisateur, $id_evenement)) {
    pageErreur(403, 'Accès réservé', 'Seuls les organisateurs de cet événement peuvent exporter la liste.', 'detail.php?id=' . $id_evenement, "Voir l'événement");
}

$inscrits = $repo->listerInscrits($id_evenement);

// Empêche Excel d'exécuter une cellule qui commence par = + - @
function celluleCsv(?string $t): string
{
    $t = (string) $t;
    return ($t !== '' && strpbrk($t[0], '=+-@') !== false) ? "'" . $t : $t;
}

// Nom de fichier simple : lettres, chiffres et tirets
$nomFichier = 'inscrits-' . $id_evenement . '-' . trim(preg_replace('/[^a-z0-9]+/', '-', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $evenement->getTitre()))), '-') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $nomFichier . '"');

$sortie = fopen('php://output', 'w');
fwrite($sortie, "\xEF\xBB\xBF"); // BOM : Excel lit bien les accents

// Séparateur « ; » : c'est celui d'Excel en français
fputcsv($sortie, ['Nom', 'Email', 'Rôle', 'Date d\'inscription', 'Présent'], ';');
foreach ($inscrits as $personne) {
    fputcsv($sortie, [
        celluleCsv($personne->getNomComplet()),
        celluleCsv($personne->getEmail()),
        $personne->estOrganisateur() ? 'Organisateur' : 'Participant',
        $personne->getDateInscription() ?? '',
        '',
    ], ';');
}
fclose($sortie);