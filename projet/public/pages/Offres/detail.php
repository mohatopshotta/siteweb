<?php
session_start();
require_once '../../includes/config.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT o.*, e.nom AS entreprise
                       FROM offre o JOIN entreprise e ON e.id_entreprise = o.id_entreprise
                       WHERE o.id_offre = ?");
$stmt->execute([$id]);
$o = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$o) { die("Offre introuvable"); }

$dejaPostule = false;
if (isset($_SESSION['id_utilisateur'])) {
    $s = $pdo->prepare("SELECT 1 FROM candidature WHERE id_utilisateur = ? AND id_offre = ?");
    $s->execute([$_SESSION['id_utilisateur'], $id]);
    $dejaPostule = (bool)$s->fetch();
}
?>
<h1><?= htmlspecialchars($o['titre']) ?></h1>
<p><?= htmlspecialchars($o['entreprise']) ?> · <?= strtoupper($o['type_offre']) ?></p>
<p><?= nl2br(htmlspecialchars($o['description'])) ?></p>
<h3>Missions</h3>
<p><?= nl2br(htmlspecialchars($o['missions'])) ?></p>

<?php if ($o['etat'] === 'cloture'): ?>
    <p><strong>Offre clôturée.</strong></p>
<?php elseif (!isset($_SESSION['id_utilisateur'])): ?>
    <p><a href="../../connexion.php">Connecte-toi</a> pour postuler.</p>
<?php elseif ($dejaPostule): ?>
    <p>Tu as déjà postulé à cette offre.</p>
<?php else: ?>
    <form method="post" action="postuler.php">
        <input type="hidden" name="id_offre" value="<?= $id ?>">
        <label>Lettre de motivation</label><br>
        <textarea name="motivation" required rows="8" cols="60"></textarea><br>
        <button type="submit">Postuler</button>
    </form>
<?php endif; ?>
