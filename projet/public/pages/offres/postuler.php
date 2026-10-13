<?php
session_start();
require_once '../../includes/config.php';

if (!isset($_SESSION['id_utilisateur']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php'); exit;
}
$idUser = $_SESSION['id_utilisateur'];
$idOffre = (int)$_POST['id_offre'];
$motivation = trim($_POST['motivation'] ?? '');

// 1. L'utilisateur est-il étudiant ou médecin ?
$s = $pdo->prepare("SELECT (SELECT COUNT(*) FROM etudiant WHERE id_utilisateur = :u)
                         + (SELECT COUNT(*) FROM medecin  WHERE id_utilisateur = :u) AS ok");
$s->execute([':u' => $idUser]);
if (!$s->fetchColumn()) { die("Seuls les étudiants et médecins peuvent postuler."); }

// 2. L'offre est-elle ouverte ? + récupérer l'email de l'auteur
$s = $pdo->prepare("SELECT o.titre, o.etat, u.email AS email_auteur
                    FROM offre o JOIN utilisateur u ON u.id_utilisateur = o.id_auteur
                    WHERE o.id_offre = ?");
$s->execute([$idOffre]);
$offre = $s->fetch(PDO::FETCH_ASSOC);
if (!$offre || $offre['etat'] !== 'ouvert') { die("Offre indisponible."); }
if ($motivation === '') { die("Lettre de motivation obligatoire."); }

// 3. Insertion (la clé primaire empêche le doublon)
try {
    $pdo->prepare("INSERT INTO candidature (id_utilisateur, id_offre, motivation) VALUES (?,?,?)")
        ->execute([$idUser, $idOffre, $motivation]);
} catch (PDOException $e) {
    die("Tu as déjà postulé.");
}

// 4. Email à l'auteur
@mail($offre['email_auteur'],
    "Nouvelle candidature : " . $offre['titre'],
    "Une nouvelle candidature a été déposée sur votre offre.");

header("Location: detail.php?id=$idOffre&ok=1");