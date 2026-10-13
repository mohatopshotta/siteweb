
<?php
// config.php démarre la session et crée la connexion $pdo
require '../../includes/config.php';

// Profil de l'utilisateur ('profil' ou 'role' selon les pages, on accepte les deux)
$profil = $_SESSION['profil'] ?? $_SESSION['role'] ?? '';

// Seuls les partenaires connectés ont le droit d'être ici
if (!isset($_SESSION['id_utilisateur']) || $profil !== 'partenaire') {
    header('Location: ../connexion.php');
    exit;
}

$id_utilisateur = $_SESSION['id_utilisateur'];

// id de l'offre dans l'URL (gerer.php?id=5). (int) transforme en nombre : pas de risque d'injection.
$id_offre = (int)($_GET['id'] ?? 0);

// On récupère l'offre et le nom de son entreprise
$stmt = $pdo->prepare(
    "SELECT o.*, e.nom AS entreprise
     FROM offre o
     JOIN entreprise e ON e.id_entreprise = o.id_entreprise
     WHERE o.id_offre = ?"
);
$stmt->execute([$id_offre]);
$offre = $stmt->fetch();

if (!$offre) {
    die("Offre introuvable.");
}

// Seul l'auteur de l'offre peut la gérer
if ($offre['id_auteur'] != $id_utilisateur) {
    die("Tu n'es pas l'auteur de cette offre.");
}

// ------------------------------------------------------------
// Actions : clic sur un des boutons de la page
// ------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action      = $_POST['action'] ?? '';
    $id_candidat = (int)($_POST['id_candidat'] ?? 0);

    if ($action === 'bloquer') {
        // Offre bloquée : plus personne ne peut postuler
        $pdo->prepare("UPDATE offre SET etat = 'cloture' WHERE id_offre = ?")->execute([$id_offre]);

    } elseif ($action === 'rouvrir') {
        $pdo->prepare("UPDATE offre SET etat = 'ouvert' WHERE id_offre = ?")->execute([$id_offre]);

    } elseif ($action === 'retenir') {
        $pdo->prepare("UPDATE candidature SET statut = 'retenue' WHERE id_offre = ? AND id_utilisateur = ?")
            ->execute([$id_offre, $id_candidat]);

    } elseif ($action === 'refuser') {
        $stmt = $pdo->prepare("UPDATE candidature SET statut = 'non_retenue' WHERE id_offre = ? AND id_utilisateur = ?");
        $stmt->execute([$id_offre, $id_candidat]);

        // rowCount() > 0 : la candidature a bien été modifiée, on envoie l'email de refus
        if ($stmt->rowCount() > 0) {
            $stmt = $pdo->prepare("SELECT prenom, email FROM utilisateur WHERE id_utilisateur = ?");
            $stmt->execute([$id_candidat]);
            $candidat = $stmt->fetch();

            $message = "Bonjour " . $candidat['prenom'] . ",\n\n"
                     . "Votre candidature à l'offre « " . $offre['titre'] . " » n'a pas été retenue.\n\n"
                     . "Cordialement,\n" . $offre['entreprise'];

            // Le @ cache l'erreur si le serveur mail n'est pas configuré (cas fréquent en local)
            @mail($candidat['email'], "Votre candidature", $message, "Content-Type: text/plain; charset=UTF-8");
        }
    }

    // On recharge la page (évite de renvoyer le formulaire avec F5)
    header('Location: gerer.php?id=' . $id_offre);
    exit;
}

// ------------------------------------------------------------
// Liste des candidats de l'offre
// ------------------------------------------------------------
$stmt = $pdo->prepare(
    "SELECT u.id_utilisateur, u.nom, u.prenom, u.email,
            c.motivation, c.statut
     FROM candidature c
     JOIN utilisateur u ON u.id_utilisateur = c.id_utilisateur
     WHERE c.id_offre = ?
     ORDER BY c.date_candidature DESC"
);
$stmt->execute([$id_offre]);
$candidats = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gérer l'offre</title>
    <link rel="stylesheet" href="../../assets/css/css.css">
</head>
<body>

<?php $racine = '../../'; require '../../includes/header.php'; ?>

<h1><?= htmlspecialchars($offre['titre']) ?></h1>
<p>
    <?= htmlspecialchars($offre['entreprise']) ?> ·
    État : <strong><?= $offre['etat'] === 'ouvert' ? 'ouverte' : 'bloquée' ?></strong> ·
    <a href="detail.php?id=<?= $id_offre ?>">voir l'offre</a>
</p>

<!-- Bouton bloquer / rouvrir selon l'état actuel -->
<form method="post">
    <?php if ($offre['etat'] === 'ouvert'): ?>
        <button type="submit" name="action" value="bloquer">Bloquer l'offre</button>
    <?php else: ?>
        <button type="submit" name="action" value="rouvrir">Rouvrir l'offre</button>
    <?php endif; ?>
</form>

<h2>Candidatures (<?= count($candidats) ?>)</h2>

<?php if (empty($candidats)): ?>
    <p>Aucune candidature pour le moment.</p>
<?php else: ?>
    <table>
        <tr>
            <th>Candidat</th>
            <th>Email</th>
            <th>Lettre de motivation</th>
            <th>Statut</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($candidats as $c): ?>
            <tr>
                <td><?= htmlspecialchars($c['prenom'] . ' ' . $c['nom']) ?></td>
                <td><?= htmlspecialchars($c['email']) ?></td>
                <td><?= nl2br(htmlspecialchars($c['motivation'])) ?></td>
                <td><?= htmlspecialchars(str_replace('_', ' ', $c['statut'])) ?></td>
                <td>
                    <form method="post">
                        <input type="hidden" name="id_candidat" value="<?= (int)$c['id_utilisateur'] ?>">
                        <button type="submit" name="action" value="retenir">Retenir</button>
                        <button type="submit" name="action" value="refuser">Refuser</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

</body>
</html>