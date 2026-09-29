<?php
require __DIR__ . '/../includes/config.php';

// accès refusé si pas connecté
if (!isset($_SESSION['id_utilisateur'])) {
    header('Location: connexion.php');
    exit;
}

$id_utilisateur = $_SESSION['id_utilisateur'];
$role = $_SESSION['role'];

// infos de base communes à tous les rôles
$req = $pdo->prepare('SELECT * FROM utilisateur WHERE id_utilisateur = ?');
$req->execute([$id_utilisateur]);
$utilisateur = $req->fetch();

// infos spécifiques selon le rôle
$infos_role = null;
if ($role === 'etudiant') {
    $req = $pdo->prepare('
        SELECT e.formation, e.cv, et.nom AS nom_etablissement
        FROM etudiant e JOIN etablissement et ON et.id_etablissement = e.id_etablissement
        WHERE e.id_utilisateur = ?
    ');
    $req->execute([$id_utilisateur]);
    $infos_role = $req->fetch();
} elseif ($role === 'medecin') {
    $req = $pdo->prepare('
        SELECT s.libelle AS specialite, h.nom AS hopital, et.nom AS nom_etablissement
        FROM medecin m
        JOIN specialite s ON s.id_specialite = m.id_specialite
        JOIN hopital h ON h.id_hopital = m.id_hopital
        LEFT JOIN etablissement et ON et.id_etablissement = m.id_etablissement
        WHERE m.id_utilisateur = ?
    ');
    $req->execute([$id_utilisateur]);
    $infos_role = $req->fetch();
} elseif ($role === 'partenaire') {
    $req = $pdo->prepare('
        SELECT p.poste, en.nom AS nom_entreprise
        FROM partenaire p JOIN entreprise en ON en.id_entreprise = p.id_entreprise
        WHERE p.id_utilisateur = ?
    ');
    $req->execute([$id_utilisateur]);
    $infos_role = $req->fetch();
}

// modification nom/prénom
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');

    if ($nom !== '' && $prenom !== '') {
        $req = $pdo->prepare('UPDATE utilisateur SET nom = ?, prenom = ? WHERE id_utilisateur = ?');
        $req->execute([$nom, $prenom, $id_utilisateur]);
        // on met à jour la session pour que le changement soit visible tout de suite
        $_SESSION['nom'] = $nom;
        $_SESSION['prenom'] = $prenom;
        $utilisateur['nom'] = $nom;
        $utilisateur['prenom'] = $prenom;
        $message = 'Profil mis à jour.';
    }
}
?>