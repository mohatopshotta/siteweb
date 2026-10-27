<?php
require_once __DIR__ . '/../../includes/config.php';

Session::exigerConnexion('../../');

$id_utilisateur = Session::getIdUtilisateur();
$role = Session::getRole();
$id_post = (int)($_GET['id'] ?? 0);

$post = (new PostManager())->trouverParId($id_post);
if ($post === null) {
    http_response_code(404);
    die('Discussion introuvable.');
}

$section = (new SectionForumManager())->trouverParId($post->getIdSection());
if (!$section->peutLire($role)) {
    http_response_code(403);
    die('Cette discussion ne vous est pas accessible.');
}

$reponseManager = new ReponseManager();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $role !== 'gestionnaire') {
    $contenu = trim($_POST['contenu'] ?? '');
    if ($contenu !== '') {
        $reponseManager->creer(new Reponse([
            'id_post'   => $id_post,
            'id_auteur' => $id_utilisateur,
            'contenu'   => $contenu,
        ]));
        Session::rediriger('post.php?id=' . $id_post . '#reponses');
    }
}

$reponses = $reponseManager->listerParPost($id_post);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($post->getTitre()) ?></title>
    <link rel="stylesheet" href="../../assets/css/css.css">
</head>
<body>

<?php $racine = '../../'; require __DIR__ . '/../../includes/header.php'; ?>

<p><a href="categorie.php?id=<?= $post->getIdSection() ?>">Retour à <?= htmlspecialchars($post->getSectionNom()) ?></a></p>

<h1><?= htmlspecialchars($post->getTitre()) ?></h1>
<p>Par <?= htmlspecialchars($post->getNomAuteur()) ?> le <?= $post->getDateFormatee() ?></p>
<div><?= nl2br(htmlspecialchars($post->getContenu())) ?></div>

<h2 id="reponses">Réponses (<?= count($reponses) ?>)</h2>
<?php foreach ($reponses as $r): ?>
    <div class="carte-forum">
        <p><?= htmlspecialchars($r->getNomAuteur()) ?> - <?= $r->getDateFormatee() ?></p>
        <p><?= nl2br(htmlspecialchars($r->getContenu())) ?></p>
    </div>
<?php endforeach; ?>

<?php if ($role !== 'gestionnaire'): ?>
    <form method="post">
        <label>Votre réponse</label><br>
        <textarea name="contenu" required></textarea><br>
        <button type="submit">Répondre</button>
    </form>
<?php endif; ?>

</body>
</html>
