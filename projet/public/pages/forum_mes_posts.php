<?php
require_once __DIR__ . '/../..//.php';
requireLogin();

$pdo = getDB();
$user = currentUser();

$stmt = $pdo->prepare(
    'SELECT p.id_post, p.titre, p.date_creation, c.nom AS categorie_nom
     FROM forum_posts p JOIN forum_categories c ON c.id_categorie = p.id_categorie
     WHERE p.id_auteur = ? ORDER BY p.date_creation DESC'
);
$stmt->execute([$user['id']]);
$mesPosts = $stmt->fetchAll();

$stmt = $pdo->prepare(
    'SELECT DISTINCT p.id_post, p.titre, c.nom AS categorie_nom, MAX(r.date_creation) AS derniere_reponse
     FROM forum_reponses r
     JOIN forum_posts p ON p.id_post = r.id_post
     JOIN forum_categories c ON c.id_categorie = p.id_categorie
     WHERE r.id_auteur = ?
     GROUP BY p.id_post, p.titre, c.nom
     ORDER BY derniere_reponse DESC'
);
$stmt->execute([$user['id']]);
$mesReponses = $stmt->fetchAll();
?>

<h1>Mes discussions</h1>

<h2>Discussions que j'ai créées</h2>
<?php if (empty($mesPosts)): ?>
    <p>Vous n'avez encore créé aucune discussion.</p>
<?php else: ?>
    <ul>
        <?php foreach ($mesPosts as $p): ?>
            <li>
                <a href="<?= SITE_URL ?>/pages/forum_post.php?id=<?= (int)$p['id_post'] ?>"><?= h($p['titre']) ?></a>
                (<?= h($p['categorie_nom']) ?>) - <?= formatDate($p['date_creation']) ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<h2>Discussions où j'ai répondu</h2>
<?php if (empty($mesReponses)): ?>
    <p>Vous n'avez encore répondu à aucune discussion.</p>
<?php else: ?>
    <ul>
        <?php foreach ($mesReponses as $p): ?>
            <li>
                <a href="<?= SITE_URL ?>/pages/forum_post.php?id=<?= (int)$p['id_post'] ?>"><?= h($p['titre']) ?></a>
                (<?= h($p['categorie_nom']) ?>)
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php require_once __DIR__ . '/../../.php'; ?>
