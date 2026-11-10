<?php
require_once __DIR__ . '/../../includes/config.php';

Session::exigerConnexion('../../');

$id_utilisateur = Session::getIdUtilisateur();
$role = Session::getRole();
$id_post = (int)($_GET['id'] ?? 0);

$postRepository = new PostRepository();
$post = $postRepository->trouverParId($id_post);
if ($post === null) {
    http_response_code(404);
    die('Discussion introuvable.');
}

$section = (new SectionForumRepository())->trouverParId($post->getIdSection());
if (!$section->peutLire($role)) {
    http_response_code(403);
    die('Cette discussion ne vous est pas accessible.');
}

$reponseRepository = new ReponseRepository();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'repondre';

    // Supprimer la discussion entière (auteur ou gestionnaire)
    if ($action === 'supprimer_post') {
        if (!$post->peutSupprimer($id_utilisateur, $role)) {
            http_response_code(403);
            die("Vous n'êtes pas autorisé à supprimer cette discussion.");
        }
        $postRepository->supprimer($id_post);
        Session::rediriger('categorie.php?id=' . $post->getIdSection() . '&msg=discussion_supprimee');
    }

    // Supprimer une réponse (auteur ou gestionnaire)
    if ($action === 'supprimer_reponse') {
        $reponse = $reponseRepository->trouverParId((int)($_POST['id_reponse'] ?? 0));
        // la réponse doit exister ET appartenir à cette discussion
        if ($reponse === null || $reponse->getIdPost() !== $id_post || !$reponse->peutSupprimer($id_utilisateur, $role)) {
            http_response_code(403);
            die("Vous n'êtes pas autorisé à supprimer cette réponse.");
        }
        $reponseRepository->supprimer($reponse->getIdReponse());
        Session::rediriger('post.php?id=' . $id_post . '&msg=reponse_supprimee#reponses');
    }

    // Répondre (le gestionnaire modère mais ne participe pas)
    if ($action === 'repondre' && $role !== 'gestionnaire') {
        $contenu = trim($_POST['contenu'] ?? '');
        if ($contenu !== '') {
            $reponseRepository->creer(new NewReponse([
                'id_post'   => $id_post,
                'id_auteur' => $id_utilisateur,
                'contenu'   => $contenu,
            ]));
            Session::rediriger('post.php?id=' . $id_post . '#reponses');
        }
    }
}

$reponses = $reponseRepository->listerParPost($id_post);

$messages = [
    'reponse_supprimee'   => 'La réponse a été supprimée.',
    'discussion_modifiee' => 'La discussion a été modifiée.',
    'reponse_modifiee'    => 'La réponse a été modifiée.',
];
$message = $messages[$_GET['msg'] ?? ''] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($post->getTitre()) ?></title>
    <link rel="stylesheet" href="../../assets/css/css.css">
</head>
<body>

<?php $racine = '../../'; require __DIR__ . '/../../includes/header.php'; ?>

<p><a href="categorie.php?id=<?= $post->getIdSection() ?>">Retour à <?= htmlspecialchars($post->getSectionNom()) ?></a></p>

<?php if ($message): ?>
    <p class="alerte alerte-succes"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<h1><?= htmlspecialchars($post->getTitre()) ?></h1>
<p>Par <?= htmlspecialchars($post->getNomAuteur()) ?> le <?= $post->getDateFormatee() ?></p>
<div class="contenu-discussion">
    <?= nl2br(htmlspecialchars($post->getContenu())) ?>

    <?php if ($post->peutModifier($id_utilisateur) || $post->peutSupprimer($id_utilisateur, $role)): ?>
        <div class="actions-message">
            <?php if ($post->peutModifier($id_utilisateur)): ?>
                <a class="btn" href="modifier.php?type=post&amp;id=<?= $id_post ?>">Modifier</a>
            <?php endif; ?>
            <form method="post" onsubmit="return confirm('Supprimer cette discussion et toutes ses réponses ?');">
                <?= Session::champCsrf() ?>
                <input type="hidden" name="action" value="supprimer_post">
                <button type="submit" class="btn btn-refuser">
                    <?= $post->peutModifier($id_utilisateur) ? 'Supprimer' : 'Supprimer (modération)' ?>
                </button>
            </form>
        </div>
    <?php endif; ?>
</div>

<h2 id="reponses">Réponses (<?= count($reponses) ?>)</h2>
<?php foreach ($reponses as $r): ?>
    <div class="carte-forum" id="reponse-<?= $r->getIdReponse() ?>">
        <p><?= htmlspecialchars($r->getNomAuteur()) ?> - <?= $r->getDateFormatee() ?></p>
        <p><?= nl2br(htmlspecialchars($r->getContenu())) ?></p>

        <?php if ($r->peutSupprimer($id_utilisateur, $role)): ?>
            <div class="actions-message">
                <?php if ($r->peutModifier($id_utilisateur)): ?>
                    <a class="btn" href="modifier.php?type=reponse&amp;id=<?= $r->getIdReponse() ?>">Modifier</a>
                <?php endif; ?>
                <form method="post" onsubmit="return confirm('Supprimer cette réponse ?');">
                    <?= Session::champCsrf() ?>
                    <input type="hidden" name="action" value="supprimer_reponse">
                    <input type="hidden" name="id_reponse" value="<?= $r->getIdReponse() ?>">
                    <button type="submit" class="btn btn-refuser">
                        <?= $r->peutModifier($id_utilisateur) ? 'Supprimer' : 'Supprimer (modération)' ?>
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

<?php if ($role !== 'gestionnaire'): ?>
    <form method="post">
        <?= Session::champCsrf() ?>
        <input type="hidden" name="action" value="repondre">
        <label for="contenu">Votre réponse</label><br>
        <textarea id="contenu" name="contenu" required></textarea><br>
        <button type="submit">Répondre</button>
    </form>
<?php endif; ?>

<?php require __DIR__ . '/' . $racine . 'includes/footer.php'; ?>
</body>
</html>
