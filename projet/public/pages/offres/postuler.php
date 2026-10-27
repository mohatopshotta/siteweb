<?php
// Traitement du formulaire "Postuler" de detail.php
require __DIR__ . '/../../includes/config.php';

// seuls les étudiants et les médecins peuvent postuler
Session::exigerRole(['etudiant', 'medecin'], '../../');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Session::rediriger('index.php');
}

$id_offre = (int)($_POST['id_offre'] ?? 0);

$offre = (new OffreManager())->trouverParId($id_offre);
if ($offre === null) {
    die("Offre introuvable.");
}

$candidature = new Candidature([
    'id_utilisateur' => Session::getIdUtilisateur(),
    'id_offre'       => $id_offre,
    'motivation'     => trim($_POST['motivation'] ?? ''),
]);

// postuler() vérifie l'offre, enregistre la candidature et prévient l'auteur par email
$erreur = (new CandidatureManager())->postuler($candidature, $offre);

if ($erreur !== '') {
    Session::rediriger('detail.php?id=' . $id_offre . '&erreur=' . urlencode($erreur));
}
Session::rediriger('detail.php?id=' . $id_offre . '&ok=1');
