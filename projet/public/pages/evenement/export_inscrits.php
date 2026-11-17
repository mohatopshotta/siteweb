<?php
require __DIR__ . '/../../includes/config.php';

Session::exigerConnexion('../../');

$repo = new EvenementRepository();
$id_utilisateur = Session::getIdUtilisateur();
$id_evenement = (int)($_GET['id'] ?? 0);

$evenement = $repo->trouverParId($id_evenement);
if ($evenement === null) {
    http_response_code(404);
    die('Événement introuvable.');
}

if (!$repo->estOrganisateur($id_utilisateur, $id_evenement)) {
    http_response_code(403);
    die('Accès réservé aux organisateurs de cet événement.');
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
