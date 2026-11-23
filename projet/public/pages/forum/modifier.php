<?php
// Modifier sa discussion ou sa réponse (réservé à l'auteur)
require_once __DIR__ . '/../../includes/config.php';

Session::exigerConnexion('../../');

$id_utilisateur = Session::getIdUtilisateur();
$type = ($_GET['type'] ?? '') === 'reponse' ? 'reponse' : 'post';
$id = (int)($_GET['id'] ?? 0);

$postRepository = new PostRepository();
$reponseRepository = new ReponseRepository();

// On charge le message à modifier et la discussion à laquelle il appartient
if ($type === 'post') {
    $message = $postRepository->trouverParId($id);
    $id_post = $id;
} else {
    $message = $reponseRepository->trouverParId($id);
    $id_post = $message ? $message->getIdPost() : 0;
}

if ($message === null) {
    http_response_code(404);
    die('Message introuvable.');
}
if (!$message->peutModifier($id_utilisateur)) {
    http_response_code(403);
    die('Vous ne pouvez modifier que vos propres messages.');
}

$titre = $type === 'post' ? $message->getTitre() : '';
$contenu = $message->getContenu();
$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre'] ?? '');
    $contenu = trim($_POST['contenu'] ?? '');

    if ($contenu === '' || ($type === 'post' && $titre === '')) {
        $erreur = $type === 'post' ? 'Merci de renseigner un titre et un contenu.' : 'La réponse ne peut pas être vide.';
    } elseif ($type === 'post' && mb_strlen($titre) > 150) {
        $erreur = 'Le titre ne doit pas dépasser 150 caractères.';
    } else {
        if ($type === 'post') {
            $postRepository->modifier($id, $id_utilisateur, $titre, $contenu);
            Session::rediriger('post.php?id=' . $id_post . '&msg=discussion_modifiee');
        }
        $reponseRepository->modifier($id, $id_utilisateur, $contenu);
        Session::rediriger('post.php?id=' . $id_post . '&msg=reponse_modifiee#reponse-' . $id);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $type === 'post' ? 'Modifier la discussion' : 'Modifier la réponse' ?></title>
    <link rel="stylesheet" href="../../assets/css/css.css">
</head>
<body>

<?php $racine = '../../'; require __DIR__ . '/../../includes/header.php'; ?>

<p><a href="post.php?id=<?= $id_post ?>">Retour à la discussion</a></p>

<h1><?= $type === 'post' ? 'Modifier la discussion' : 'Modifier ma réponse' ?></h1>

<?php if ($erreur): ?>
    <p class="alerte alerte-erreur"><?= htmlspecialchars($erreur) ?></p>
<?php endif; ?>

<form method="post">
    <?= Session::champCsrf() ?>
    <?php if ($type === 'post'): ?>
        <label for="titre">Titre</label>
        <input type="text" id="titre" name="titre" maxlength="150" required value="<?= htmlspecialchars($titre) ?>">
    <?php endif; ?>

    <label for="contenu"><?= $type === 'post' ? 'Message' : 'Réponse' ?></label>
    <textarea id="contenu" name="contenu" required><?= htmlspecialchars($contenu) ?></textarea>

    <button type="submit">Enregistrer</button>
</form>

<?php require __DIR__ . '/' . $racine . 'includes/footer.php'; ?>
</body>
</html>
