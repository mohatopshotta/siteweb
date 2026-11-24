<?php
require __DIR__ . '/../../includes/config.php';

Session::exigerRole(['etudiant', 'medecin'], '../../');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Session::rediriger('index.php');
}
Session::verifierCsrf();


$id_offre = (int)($_POST['id_offre'] ?? 0);

$offre = (new OffreRepository())->trouverParId($id_offre);
if ($offre === null) {
    die("Offre introuvable.");
}

$candidature = new NewCandidature([
    'id_utilisateur' => Session::getIdUtilisateur(),
    'id_offre'       => $id_offre,
    'motivation'     => trim($_POST['motivation'] ?? ''),
]);

// CV joint (facultatif, étudiants seulement) : il remplace celui du profil
$nouveau_cv = null;
if (Session::aLeRole(['etudiant']) && FichierCv::estEnvoye($_FILES['cv'] ?? null)) {
    $erreur_cv = '';
    $nouveau_cv = FichierCv::enregistrer($_FILES['cv'], $erreur_cv);
    if ($nouveau_cv === null) {
        Session::rediriger('detail.php?id=' . $id_offre . '&erreur=' . urlencode($erreur_cv));
    }
}

$erreur = (new CandidatureRepository())->postuler($candidature, $offre);

if ($nouveau_cv !== null) {
    if ($erreur === '') {
        // Candidature enregistrée : le nouveau CV devient celui du profil (l'ancien fichier est supprimé)
        (new UtilisateurRepository())->changerCv(Session::getIdUtilisateur(), $nouveau_cv);
    } else {
        // Candidature refusée (déjà postulé, offre clôturée...) : on ne garde pas le fichier envoyé
        @unlink(__DIR__ . '/../../uploads/cv/' . basename($nouveau_cv));
    }
}

if ($erreur !== '') {
    Session::rediriger('detail.php?id=' . $id_offre . '&erreur=' . urlencode($erreur));
}
Session::rediriger('detail.php?id=' . $id_offre . '&ok=1');
