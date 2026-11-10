<?php
require_once __DIR__ . '/../../includes/config.php';

Session::exigerMembre('../../');

$id_utilisateur = Session::getIdUtilisateur();
$postRepository = new PostRepository();

$mes_posts = $postRepository->listerParAuteur($id_utilisateur);
$mes_reponses = $postRepository->listerOuUtilisateurARepondu($id_utilisateur);
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
                <a href="post.php?id=<?= $p->getIdPost() ?>"><?= htmlspecialchars($p->getTitre()) ?></a>
                (<?= htmlspecialchars($p->getSectionNom()) ?>) - <?= $p->getDateFormatee() ?>
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
                <a href="post.php?id=<?= $p->getIdPost() ?>"><?= htmlspecialchars($p->getTitre()) ?></a>
                (<?= htmlspecialchars($p->getSectionNom()) ?>)
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<p><a href="liste.php">Retour au forum</a></p>

<?php require __DIR__ . '/' . $racine . 'includes/footer.php'; ?>
</body>
</html>
