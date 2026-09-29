<?php
require_once __DIR__ . '/../../.php';

$pdo = getDB();
$user = currentUser();
$idPost = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare(
    'SELECT p.*, c.code AS categorie_code, c.nom AS categorie_nom, u.nom, u.prenom
     FROM forum_posts p
     JOIN forum_categories c ON c.id_categorie = p.id_categorie
     JOIN utilisateurs u ON u.id_utilisateur = p.id_auteur
     WHERE p.id_post = ?'
);
$stmt->execute([$idPost]);
$post = $stmt->fetch();
if (!$post) {
    http_response_code(404);
    die('Discussion introuvable.');
}


$peutLire = true;
if ($post['categorie_code'] === 'medecins') {
    $peutLire = $user && in_array($user['role'], ['medecin', 'partenaire', 'gestionnaire'], true);
} elseif ($post['categorie_code'] === 'etudiants') {
    $peutLire = $user && in_array($user['role'], ['etudiant', 'medecin', 'gestionnaire'], true);
}
if (!$peutLire) {
    http_response_code(403);
    die('Cette discussion ne vous est pas accessible.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['repondre'])) {
    requireLogin();
    if (checkCsrf()) {
        $contenu = trim($_POST['contenu'] ?? '');
        if ($contenu !== '') {
            $stmt = $pdo->prepare('INSERT INTO forum_reponses (id_post, id_auteur, contenu) VALUES (?, ?, ?)');
            $stmt->execute([$idPost, $user['id'], $contenu]);
            header('Location: ' . SITE_URL . '/pages/forum_post.php?id=' . $idPost . '#reponses');
            exit;
        }
    }
}

$stmt = $pdo->prepare(
    'SELECT r.*, u.nom, u.prenom FROM forum_reponses r
     JOIN utilisateurs u ON u.id_utilisateur = r.id_auteur
     WHERE r.id_post = ? ORDER BY r.date_creation'
);
$stmt->execute([$idPost]);
$reponses = $stmt->fetchAll();
?>

<p class="meta"><a href="<?= SITE_URL ?>/pages/forum_categorie.php?code=<?= h($post['categorie_code']) ?>">&larr; <?= h($post['categorie_nom']) ?></a></p>

<h1><?= h($post['titre']) ?></h1>
<p class="meta">Par <?= h($post['prenom'] . ' ' . $post['nom']) ?> le <?= formatDate($post['date_creation']) ?></p>
<div class="post-content"><?= nl2br(h($post['contenu'])) ?></div>

<h2 id="reponses">Réponses (<?= count($reponses) ?>)</h2>
<?php foreach ($reponses as $r): ?>
    <div class="reponse">
        <p class="meta"><?= h($r['prenom'] . ' ' . $r['nom']) ?> - <?= formatDate($r['date_creation']) ?></p>
        <p><?= nl2br(h($r['contenu'])) ?></p>
    </div>
<?php endforeach; ?>

<?php if ($user): ?>
    <form method="post" class="form">
        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
        <input type="hidden" name="repondre" value="1">
        <label>Votre réponse <textarea name="contenu" required></textarea></label>
        <button type="submit">Répondre</button>
    </form>
<?php else: ?>
    <p><a href="<?= SITE_URL ?>/pages/connexion.php">Connectez-vous</a> pour répondre.</p>
<?php endif; ?>

<?php require_once __DIR__ . '/../..//.php'; ?>
