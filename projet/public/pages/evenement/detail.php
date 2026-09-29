<?php
session_start();
require '../../includes/config.php';

// ------------------------------------------------------------
// Accès réservé aux utilisateurs connectés (sauf gestionnaires)
// ------------------------------------------------------------
if (!isset($_SESSION['id_utilisateur']) || $_SESSION['profil'] === 'gestionnaire') {
    header('Location: ../connexion.php');
    exit;
}

$id_evenement = $_GET['id'] ?? null;
if (!$id_evenement || !ctype_digit((string)$id_evenement)) {
    die("Événement introuvable.");
}

$id_utilisateur = $_SESSION['id_utilisateur'];
$message = '';

// ------------------------------------------------------------
// Récupération de l'événement
// ------------------------------------------------------------
$stmt = $pdo->prepare("SELECT * FROM evenement WHERE id_evenement = :id");
$stmt->execute([':id' => $id_evenement]);
$evenement = $stmt->fetch();

if (!$evenement) {
    die("Événement introuvable.");
}

// ------------------------------------------------------------
// Nombre de places déjà prises
// ------------------------------------------------------------
$stmt = $pdo->prepare("SELECT COUNT(*) FROM inscription_evenement WHERE id_evenement = :id");
$stmt->execute([':id' => $id_evenement]);
$places_prises = $stmt->fetchColumn();
$places_restantes = $evenement['nombre_places'] - $places_prises;

// ------------------------------------------------------------
// Est-ce que l'utilisateur connecté est déjà inscrit / organisateur ?
// ------------------------------------------------------------
$stmt = $pdo->prepare(
    "SELECT organiser FROM inscription_evenement
     WHERE id_utilisateur = :id_utilisateur AND id_evenement = :id_evenement"
);
$stmt->execute([':id_utilisateur' => $id_utilisateur, ':id_evenement' => $id_evenement]);
$inscription = $stmt->fetch();

$deja_inscrit = $inscription !== false;
$est_organisateur = $deja_inscrit && (int)$inscription['organiser'] === 1;

// ------------------------------------------------------------
// Traitement de l'inscription
// ------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['inscription'])) {

    if ($deja_inscrit) {
        $message = "Tu es déjà inscrit à cet événement.";
    } elseif ($places_restantes <= 0) {
        $message = "Il n'y a plus de places disponibles.";
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO inscription_evenement (id_utilisateur, id_evenement, organiser)
             VALUES (:id_utilisateur, :id_evenement, 0)"
        );
        $stmt->execute([
            ':id_utilisateur' => $id_utilisateur,
            ':id_evenement'   => $id_evenement,
        ]);

        // on recharge la page pour mettre à jour les infos affichées
        header('Location: detail.php?id=' . $id_evenement);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($evenement['titre']) ?></title>
    <link rel="stylesheet" href="../../assets/css/css.css">
</head>
<body>

<a href="liste.php">&larr; Retour à la liste des événements</a>

<h1><?= htmlspecialchars($evenement['titre']) ?></h1>
<p><em><?= htmlspecialchars($evenement['type_evenement']) ?></em></p>

<p><?= nl2br(htmlspecialchars($evenement['description'])) ?></p>

<p><strong>Lieu :</strong> <?= htmlspecialchars($evenement['lieu']) ?></p>

<?php if (!empty($evenement['elements_requis'])): ?>
    <p><strong>Éléments requis :</strong> <?= nl2br(htmlspecialchars($evenement['elements_requis'])) ?></p>
<?php endif; ?>

<p><strong>Places :</strong> <?= $places_restantes ?> restante(s) sur <?= $evenement['nombre_places'] ?></p>

<?php if (!empty($message)): ?>
    <p class="message"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<?php if ($est_organisateur): ?>
    <p>Tu es organisateur de cet événement.</p>
    <a href="gerer.php?id=<?= $id_evenement ?>">Gérer l'événement</a>

<?php elseif ($deja_inscrit): ?>
    <p>Tu es inscrit à cet événement.</p>

<?php elseif ($places_restantes > 0): ?>
    <form method="post" action="detail.php?id=<?= $id_evenement ?>">
        <button type="submit" name="inscription">S'inscrire</button>
    </form>

<?php else: ?>
    <p>Il n'y a plus de places disponibles.</p>
<?php endif; ?>

</body>
</html>
