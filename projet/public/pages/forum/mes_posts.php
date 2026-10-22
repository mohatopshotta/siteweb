<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/forum_acces.php';

if (!isset($_SESSION['id_utilisateur']) || $_SESSION['profil'] === 'gestionnaire') {
    header('Location: ../connexion.php');
    exit;
}

$id_utilisateur = $_SESSION['id_utilisateur'];

$req = $pdo->prepare(
    'SELECT p.id_post, p.titre, p.date_heure, s.nom AS section_nom
     FROM post p
     JOIN section_forum s ON s.id_section = p.id_section
     WHERE p.id_auteur = ?
     ORDER BY p.date_heure DESC'
);
$req->execute([$id_utilisateur]);
$mes_posts = $req->fetchAll();

$req = $pdo->prepare(
    'SELECT p.id_post, p.titre, s.nom AS section_nom, MAX(r.date_heure) AS derniere_reponse
     FROM reponse r
     JOIN post p ON p.id_post = r.id_post
     JOIN section_forum s ON s.id_section = p.id_section
     WHERE r.id_auteur = ?
     GROUP BY p.id_post, p.titre, s.nom
     ORDER BY derniere_reponse DESC'
);
$req->execute([$id_utilisateur]);
$mes_reponses = $req->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes discussions</title>
    <link rel="stylesheet" href="../../assets/css/css.css">
</head>
<body>

<?php $racine = '../../'; require __DIR__ . '/../../includes/header.php'; ?>

<h1>Mes discussions</h1>

<h2>Discussions que j'ai créées</h2>
<?php if (empty($mes_posts)): ?>
    <p>Vous n'avez encore créé aucune discussion.</p>
<?php else: ?>
    <ul>
        <?php foreach ($mes_posts as $p): ?>
            <li>
                <a href="post.php?id=<?= (int)$p['id_post'] ?>"><?= htmlspecialchars($p['titre']) ?></a>
                (<?= htmlspecialchars($p['section_nom']) ?>) - <?= forum_date($p['date_heure']) ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<h2>Discussions où j'ai répondu</h2>
<?php if (empty($mes_reponses)): ?>
    <p>Vous n'avez encore répondu à aucune discussion.</p>
<?php else: ?>
    <ul>
        <?php foreach ($mes_reponses as $p): ?>
            <li>
                <a href="post.php?id=<?= (int)$p['id_post'] ?>"><?= htmlspecialchars($p['titre']) ?></a>
                (<?= htmlspecialchars($p['section_nom']) ?>)
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<p><a href="liste.php">Retour au forum</a></p>

</body>
</html>
