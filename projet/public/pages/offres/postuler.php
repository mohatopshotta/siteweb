<?php
require __DIR__ . '/../../includes/config.php';

Session::exigerRole(['etudiant', 'medecin'], '../../');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Session::rediriger('index.php');
}

$id_offre = (int)($_POST['id_offre'] ?? 0);

$offre = (new OffreRepository())->trouverParId($id_offre);
if ($offre === null) {
    die("NewOffre introuvable.");
}

$candidature = new NewCandidature([
    'id_utilisateur' => Session::getIdUtilisateur(),
    'id_offre'       => $id_offre,
    'motivation'     => trim($_POST['motivation'] ?? ''),
]);

$erreur = (new CandidatureRepository())->postuler($candidature, $offre);

if ($erreur !== '') {
    Session::rediriger('detail.php?id=' . $id_offre . '&erreur=' . urlencode($erreur));
}
Session::rediriger('detail.php?id=' . $id_offre . '&ok=1');
