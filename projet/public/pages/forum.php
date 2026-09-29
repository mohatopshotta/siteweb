<?php
require_once __DIR__ . '/../..//';

$pdo = getDB();
$categories = $pdo->query(
    "SELECT c.*, (SELECT COUNT(*) FROM forum_posts p WHERE p.id_categorie = c.id_categorie) AS nb_posts
     FROM forum_categories c ORDER BY c.id_categorie"
)->fetchAll();

$user = currentUser();
?>

<h1>Forum</h1>
<p>Choisissez une section pour consulter ou créer une discussion.</p>

<div class="card-grid">
    <?php foreach ($categories as $c): ?>
        <article class="card">
            <h3><?= h($c['nom']) ?></h3>
            <p class="meta"><?= (int)$c['nb_posts'] ?> discussion(s)</p>
            <?php if ($c['code'] === 'medecins'): ?>
                <p class="meta">Réservée aux médecins et partenaires</p>
            <?php elseif ($c['code'] === 'etudiants'): ?>
                <p class="meta">Ouverte aux étudiants et médecins (création réservée aux étudiants)</p>
            <?php else: ?>
                <p class="meta">Ouverte à tous les membres</p>
            <?php endif; ?>
            <a href="<?= SITE_URL ?>/pages/forum_categorie.php?code=<?= h($c['code']) ?>">Accéder &rarr;</a>
        </article>
    <?php endforeach; ?>
</div>

<?php if ($user): ?>
    <p><a href="<?= SITE_URL ?>/pages/forum_mes_posts.php">Mes discussions et réponses</a></p>
<?php endif; ?>

<?php require_once __DIR__ . '/../..//'; ?>
