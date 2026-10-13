<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/forum_acces.php';

if (!isset($_SESSION['id_utilisateur'])) {
    header('Location: ../connexion.php');
    exit;
}

$id_utilisateur = $_SESSION['id_utilisateur'];
$role = $_SESSION['role'];
$id_post = (int)($_GET['id'] ?? 0);

$req = $pdo->prepare(
    'SELECT p.*, s.nom AS section_nom, u.nom, u.prenom
     FROM post p
     JOIN section_forum s ON s.id_section = p.id_section
     JOIN utilisateur u ON u.id_utilisateur = p.id_auteur
     WHERE p.id_post = ?'
);
$req->execute([$id_post]);
$post = $req->fetch();

if (!$post) {
    http_response_code(404);
    die('Discussion introuvable.');
}

$droits = forum_droits((int)$post['id_section'], $role);
if (!$droits['lire']) {
    http_response_code(403);
    die('Cette discussion ne vous est pas accessible.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $role !== 'gestionnaire') {
    $contenu = trim($_POST['contenu'] ?? '');
    if ($contenu !== '') {
        $req = $pdo->prepare('INSERT INTO reponse (id_post, id_auteur, contenu) VALUES (?, ?, ?)');
        $req->execute([$id_post, $id_utilisateur, $contenu]);
        header('Location: post.php?id=' . $id_post . '#reponses');
        exit;
    }
}

$req = $pdo->prepare(
    'SELECT r.contenu, r.date_heure, u.nom, u.prenom
     FROM reponse r
     JOIN utilisateur u ON u.id_utilisateur = r.id_auteur
     WHERE r.id_post = ?
     ORDER BY r.date_heure'
);
$req->execute([$id_post]);
$reponses = $req->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($post['titre']) ?></title>
    <link rel="stylesheet" href="../../assets/css/css.css">
</head>
<body>

<?php $racine = '../../'; require __DIR__ . '/../../includes/header.php'; ?>

<p><a href="categorie.php?id=<?= (int)$post['id_section'] ?>">Retour à <?= htmlspecialchars($post['section_nom']) ?></a></p>

<h1><?= htmlspecialchars($post['titre']) ?></h1>
<p>Par <?= htmlspecialchars($post['prenom'] . ' ' . $post['nom']) ?> le <?= forum_date($post['date_heure']) ?></p>
<div><?= nl2br(htmlspecialchars($post['contenu'])) ?></div>

<h2 id="reponses">Réponses (<?= count($reponses) ?>)</h2>
<?php foreach ($reponses as $r): ?>
    <div class="carte-forum">
        <p><?= htmlspecialchars($r['prenom'] . ' ' . $r['nom']) ?> - <?= forum_date($r['date_heure']) ?></p>
        <p><?= nl2br(htmlspecialchars($r['contenu'])) ?></p>
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
