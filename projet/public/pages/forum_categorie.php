<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/forum_acces.php';

if (!isset($_SESSION['id_utilisateur'])) {
    header('Location: connexion.php');
    exit;
}

$id_utilisateur = $_SESSION['id_utilisateur'];
$role = $_SESSION['role'];
$id_section = (int)($_GET['id'] ?? 0);

$req = $pdo->prepare('SELECT * FROM section_forum WHERE id_section = ?');
$req->execute([$id_section]);
$section = $req->fetch();

if (!$section) {
    http_response_code(404);
    die('Section de forum introuvable.');
}

$droits = forum_droits($id_section, $role);
if (!$droits['lire']) {
    http_response_code(403);
    die('Cette section du forum ne vous est pas accessible.');
}

$erreur = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$droits['creer']) {
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
        $req = $pdo->prepare('INSERT INTO post (id_section, id_auteur, titre, contenu) VALUES (?, ?, ?, ?)');
        $req->execute([$id_section, $id_utilisateur, $titre, $contenu]);
        header('Location: forum_categorie.php?id=' . $id_section);
        exit;
    }
}

$req = $pdo->prepare(
    'SELECT p.id_post, p.titre, p.date_heure, u.nom, u.prenom,
            (SELECT COUNT(*) FROM reponse r WHERE r.id_post = p.id_post) AS nb_reponses
     FROM post p
     JOIN utilisateur u ON u.id_utilisateur = p.id_auteur
     WHERE p.id_section = ?
     ORDER BY p.date_heure DESC'
);
$req->execute([$id_section]);
$posts = $req->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($section['nom']) ?></title>
    <link rel="stylesheet" href="../assets/css/css.css">
</head>
<body>

<?php $racine = '../'; require __DIR__ . '/../includes/header.php'; ?>

<h1><?= htmlspecialchars($section['nom']) ?></h1>

<?php if ($erreur): ?>
    <p style="color:red;"><?= htmlspecialchars($erreur) ?></p>
<?php endif; ?>

<?php if ($droits['creer']): ?>
    <details>
        <summary>Nouvelle discussion</summary>
        <form method="post">
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
        <h3><a href="forum_post.php?id=<?= (int)$p['id_post'] ?>"><?= htmlspecialchars($p['titre']) ?></a></h3>
        <p>
            Par <?= htmlspecialchars($p['prenom'] . ' ' . $p['nom']) ?> le <?= forum_date($p['date_heure']) ?>
            - <?= (int)$p['nb_reponses'] ?> réponse(s)
        </p>
    </div>
<?php endforeach; ?>

<p><a href="forum.php">Retour au forum</a></p>

</body>
</html>
