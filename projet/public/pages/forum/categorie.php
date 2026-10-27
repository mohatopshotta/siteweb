<?php
require_once __DIR__ . '/../../includes/config.php';

Session::exigerConnexion('../../');

$id_utilisateur = Session::getIdUtilisateur();
$role = Session::getRole();
$id_section = (int)($_GET['id'] ?? 0);

$section = (new SectionForumManager())->trouverParId($id_section);
if ($section === null) {
    http_response_code(404);
    die('Section de forum introuvable.');
}

if (!$section->peutLire($role)) {
    http_response_code(403);
    die('Cette section du forum ne vous est pas accessible.');
}

$postManager = new PostManager();

$erreur = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$section->peutCreer($role)) {
        http_response_code(403);
        die("Vous n'êtes pas autorisé à créer une discussion dans cette section.");
    }

    $titre = trim($_POST['titre'] ?? '');
    $contenu = trim($_POST['contenu'] ?? '');

    if ($titre === '' || $contenu === '') {
        $erreur = 'Merci de renseigner un titre et un contenu.';
    } elseif (mb_strlen($titre) > 150) {
        $erreur = 'Le titre ne doit pas dépasser 150 caractères.';
    } else {
        $postManager->creer(new NewPost([
            'id_section' => $id_section,
            'id_auteur'  => $id_utilisateur,
            'titre'      => $titre,
            'contenu'    => $contenu,
        ]));
        Session::rediriger('categorie.php?id=' . $id_section);
    }
}

$posts = $postManager->listerParSection($id_section);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($section->getNom()) ?></title>
    <link rel="stylesheet" href="../../assets/css/css.css">
</head>
<body>

<?php $racine = '../../'; require __DIR__ . '/../../includes/header.php'; ?>

<h1><?= htmlspecialchars($section->getNom()) ?></h1>

<?php if ($erreur): ?>
    <p style="color:red;"><?= htmlspecialchars($erreur) ?></p>
<?php endif; ?>

<?php if ($section->peutCreer($role)): ?>
    <details>
        <summary>Nouvelle discussion</summary>
        <form method="post">
            <?= Session::champCsrf() ?>
            <label>Titre</label>
            <input type="text" name="titre" maxlength="150" required><br>
            <label>Message</label>
            <textarea name="contenu" required></textarea><br>
            <button type="submit">Publier</button>
        </form>
    </details>
<?php endif; ?>

<?php if (empty($posts)): ?>
    <p>Aucune discussion pour le moment.</p>
<?php endif; ?>

<?php foreach ($posts as $p): ?>
    <div class="carte-forum">
        <h3><a href="post.php?id=<?= $p->getIdPost() ?>"><?= htmlspecialchars($p->getTitre()) ?></a></h3>
        <p>
            Par <?= htmlspecialchars($p->getNomAuteur()) ?> le <?= $p->getDateFormatee() ?>
            - <?= $p->getNbReponses() ?> réponse(s)
        </p>
    </div>
<?php endforeach; ?>

<p><a href="liste.php">Retour au forum</a></p>

</body>
</html>
