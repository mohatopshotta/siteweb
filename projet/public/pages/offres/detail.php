<?php
/** @var PDO $pdo */
require_once '../../includes/config.php'; // session_start() est déjà dedans

$id = (int)($_GET['id'] ?? 0);
$idUser = $_SESSION['id_utilisateur'] ?? null;

// --- Traitement des actions de l'auteur (clôturer / rouvrir / cacher une candidature) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $idUser) {

    if (isset($_POST['toggle_etat'])) {
        $pdo->prepare("UPDATE offre SET etat = ? WHERE id_offre = ? AND id_auteur = ?")
                ->execute([$_POST['toggle_etat'] === 'cloture' ? 'cloture' : 'ouvert', $id, $idUser]);
    }

    if (isset($_POST['refuser_candidat'])) {
        $idCandidat = (int)$_POST['refuser_candidat'];

        // Vérifie que $idUser est bien l'auteur de l'offre avant de toucher à la candidature
        $check = $pdo->prepare("SELECT 1 FROM offre WHERE id_offre = ? AND id_auteur = ?");
        $check->execute([$id, $idUser]);
        if ($check->fetch()) {
            $pdo->prepare("UPDATE candidature SET statut = 'non_retenue' WHERE id_utilisateur = ? AND id_offre = ?")
                    ->execute([$idCandidat, $id]);

            $mail = $pdo->prepare("SELECT email FROM utilisateur WHERE id_utilisateur = ?");
            $mail->execute([$idCandidat]);
            if ($email = $mail->fetchColumn()) {
                @mail($email, "Candidature non retenue", "Votre candidature n'a pas été retenue.");
            }
        }
    }

    header("Location: detail.php?id=$id");
    exit;
}

// --- Chargement de l'offre (LEFT JOIN : une offre du groupe n'a pas d'entreprise) ---
$stmt = $pdo->prepare(
        "SELECT o.*, COALESCE(e.nom, 'Groupe GDH') AS entreprise, u.nom AS auteur_nom, u.prenom AS auteur_prenom
     FROM offre o
     LEFT JOIN entreprise e ON e.id_entreprise = o.id_entreprise
     JOIN utilisateur u ON u.id_utilisateur = o.id_auteur
     WHERE o.id_offre = ?"
);
$stmt->execute([$id]);
$o = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$o) { die("Offre introuvable"); }

$estAuteur = $idUser && (int)$o['id_auteur'] === (int)$idUser;

// --- Est-ce que l'utilisateur connecté a déjà postulé ? ---
$dejaPostule = false;
if ($idUser) {
    $s = $pdo->prepare("SELECT 1 FROM candidature WHERE id_utilisateur = ? AND id_offre = ?");
    $s->execute([$idUser, $id]);
    $dejaPostule = (bool)$s->fetch();
}

// --- Si c'est l'auteur : liste des candidatures (hors refusées = "cachées") ---
$candidats = [];
if ($estAuteur) {
    $s = $pdo->prepare(
            "SELECT c.*, u.nom, u.prenom, u.email
         FROM candidature c
         JOIN utilisateur u ON u.id_utilisateur = c.id_utilisateur
         WHERE c.id_offre = ? AND c.statut != 'non_retenue'
         ORDER BY c.date_candidature DESC"
    );
    $s->execute([$id]);
    $candidats = $s->fetchAll(PDO::FETCH_ASSOC);
}
?>
    <h1><?= htmlspecialchars($o['titre']) ?></h1>
    <p><?= htmlspecialchars($o['entreprise']) ?> · <?= strtoupper($o['type_offre']) ?>
        <?= $o['salaire'] ? ' · ' . htmlspecialchars($o['salaire']) . ' €' : '' ?>
        · Publiée par <?= htmlspecialchars($o['auteur_prenom'] . ' ' . $o['auteur_nom']) ?></p>
    <p><?= nl2br(htmlspecialchars($o['description'])) ?></p>
    <h3>Missions</h3>
    <p><?= nl2br(htmlspecialchars($o['missions'])) ?></p>

<?php if ($estAuteur): ?>
    <hr>
    <h2>Gestion de l'offre</h2>
    <form method="post">
        <?php if ($o['etat'] === 'ouvert'): ?>
            <input type="hidden" name="toggle_etat" value="cloture">
            <button type="submit">Clôturer l'offre à la postulation</button>
        <?php else: ?>
            <input type="hidden" name="toggle_etat" value="ouvert">
            <button type="submit">Rouvrir l'offre</button>
        <?php endif; ?>
    </form>

    <h3>Candidatures reçues (<?= count($candidats) ?>)</h3>
    <?php if (!$candidats): ?>
        <p>Aucune candidature pour le moment.</p>
    <?php else: ?>
        <?php foreach ($candidats as $c): ?>
            <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
                <p><strong><?= htmlspecialchars($c['prenom'] . ' ' . $c['nom']) ?></strong>
                    (<?= htmlspecialchars($c['email']) ?>) — <?= htmlspecialchars($c['statut']) ?></p>
                <p><?= nl2br(htmlspecialchars($c['motivation'])) ?></p>
                <form method="post" onsubmit="return confirm('Refuser cette candidature ?');">
                    <input type="hidden" name="refuser_candidat" value="<?= (int)$c['id_utilisateur'] ?>">
                    <button type="submit">Refuser (cacher)</button>
                </form>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

<?php elseif ($o['etat'] === 'cloture'): ?>
    <p><strong>Offre clôturée.</strong></p>
<?php elseif (!$idUser): ?>
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