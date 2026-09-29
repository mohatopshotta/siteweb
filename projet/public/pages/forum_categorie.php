<?php
require_once __DIR__ . '/../..//.php';

$pdo = getDB();
$user = currentUser();
$code = $_GET['code'] ?? '';

$stmt = $pdo->prepare('SELECT * FROM forum_categories WHERE code = ?');
$stmt->execute([$code]);
$categorie = $stmt->fetch();
if (!$categorie) {
    http_response_code(404);
    die('Section de forum introuvable.');
}

$peutLire = true;
$peutCreerPost = false;
if ($user) {
    if ($categorie['code'] === 'generale') {
        $peutCreerPost = true;
    } elseif ($categorie['code'] === 'medecins') {
        $peutLire = in_array($user['role'], ['medecin', 'partenaire', 'gestionnaire'], true);
        $peutCreerPost = in_array($user['role'], ['medecin', 'partenaire'], true);
    } elseif ($categorie['code'] === 'etudiants') {
        $peutLire = in_array($user['role'], ['etudiant', 'medecin', 'gestionnaire'], true);
        $peutCreerPost = $user['role'] === 'etudiant';
    }
} else {
    $peutLire = $categorie['code'] === 'generale';
}

if (!$peutLire) {
    http_response_code(403);
    die('Cette section du forum ne vous est pas accessible.');
}

$erreurs = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['creer_post'])) {
    requireLogin();
    if (!$peutCreerPost) {
        http_response_code(403);
        die('Vous n\'êtes pas autorisé à créer une discussion dans cette section.');
    }
    if (!checkCsrf()) {
        $erreurs[] = 'Jeton de sécurité invalide.';
    } else {
        $titre = trim($_POST['titre'] ?? '');
        $contenu = trim($_POST['contenu'] ?? '');
        if ($titre === '' || $contenu === '') {
            $erreurs[] = 'Merci de renseigner un titre et un contenu.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO forum_posts (id_categorie, id_auteur, titre, contenu) VALUES (?, ?, ?, ?)');
            $stmt->execute([$categorie['id_categorie'], $user['id'], $titre, $contenu]);
        }
    }
}

$stmt = $pdo->prepare(
    "SELECT p.*, u.nom, u.prenom,
     (SELECT COUNT(*) FROM forum_reponses r WHERE r.id_post = p.id_post) AS nb_reponses
     FROM forum_posts p JOIN utilisateurs u ON u.id_utilisateur = p.id_auteur
     WHERE p.id_categorie = ? ORDER BY p.date_creation DESC"
);
$stmt->execute([$categorie['id_categorie']]);
$posts = $stmt->fetchAll();
?>

<h1><?= h($categorie['nom']) ?></h1>

<?php foreach ($erreurs as $err): ?><p class="alert alert-error"><?= h($err) ?></p><?php endforeach; ?>

<?php if ($peutCreerPost): ?>
    <details class="form-collapsible">
        <summary>Nouvelle discussion</summary>
        <form method="post" class="form">
            <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
            <input type="hidden" name="creer_post" value="1">
            <label>Titre <input type="text" name="titre" required></label>
            <label>Message <textarea name="contenu" required></textarea></label>
            <button type="submit">Publier</button>
        </form>
    </details>
<?php elseif (!$user): ?>
    <p><a href="<?= SITE_URL ?>/pages/connexion.php">Connectez-vous</a> pour participer.</p>
<?php endif; ?>

<div class="post-list">
    <?php if (empty($posts)): ?>
        <p>Aucune discussion pour le moment.</p>
    <?php endif; ?>
    <?php foreach ($posts as $p): ?>
        <article class="post-summary">
            <h3><a href="<?= SITE_URL ?>/pages/forum_post.php?id=<?= (int)$p['id_post'] ?>"><?= h($p['titre']) ?></a></h3>
            <p class="meta">
                Par <?= h($p['prenom'] . ' ' . $p['nom']) ?> le <?= formatDate($p['date_creation']) ?>
                - <?= (int)$p['nb_reponses'] ?> réponse(s)
            </p>
        </article>
    <?php endforeach; ?>
</div>

<p><a href="<?= SITE_URL ?>/pages/forum.php">&larr; Retour au forum</a></p>

<?php require_once __DIR__ . '/../..//.php'; ?>
