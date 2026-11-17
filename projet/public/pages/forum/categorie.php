<?php
require_once __DIR__ . '/../../includes/config.php';

Session::exigerConnexion('../../');

$id_utilisateur = Session::getIdUtilisateur();
$role = Session::getRole();
$id_section = (int)($_GET['id'] ?? 0);

$section = (new SectionForumRepository())->trouverParId($id_section);
if ($section === null) {
    http_response_code(404);
    die('Section de forum introuvable.');
}

if (!$section->peutLire($role)) {
    http_response_code(403);
    die('Cette section du forum ne vous est pas accessible.');
}

$postRepository = new PostRepository();

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
        $postRepository->creer(new NewPost([
            'id_section' => $id_section,
            'id_auteur'  => $id_utilisateur,
            'titre'      => $titre,
            'contenu'    => $contenu,
        ]));
        Session::rediriger('categorie.php?id=' . $id_section);
    }
}

// Recherche et pagination (paramètres GET, conservés dans les liens de page)
$recherche = trim($_GET['q'] ?? '');
$parPage = 10;
$total = $postRepository->compterParSection($id_section, $recherche);
$nbPages = max(1, (int)ceil($total / $parPage));
$page = min(max(1, (int)($_GET['page'] ?? 1)), $nbPages);

$posts = $postRepository->listerParSection($id_section, $recherche, $page, $parPage);

// Construit le lien vers une page en gardant la section et la recherche
function lienPage(int $idSection, string $recherche, int $page): string
{
    $params = ['id' => $idSection, 'page' => $page];
    if ($recherche !== '') {
        $params['q'] = $recherche;
    }
    return 'categorie.php?' . http_build_query($params);
}
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

<?php if (($_GET['msg'] ?? '') === 'discussion_supprimee'): ?>
    <p class="alerte alerte-succes">La discussion et ses réponses ont été supprimées.</p>
<?php endif; ?>

<?php if ($erreur): ?>
    <p class="alerte alerte-erreur"><?= htmlspecialchars($erreur) ?></p>
<?php endif; ?>

<?php if ($section->peutCreer($role)): ?>
    <details>
        <summary>Nouvelle discussion</summary>
        <form method="post">
            <?= Session::champCsrf() ?>
            <label for="titre">Titre</label>
            <input type="text" id="titre" name="titre" maxlength="150" required><br>
            <label for="contenu">Message</label>
            <textarea id="contenu" name="contenu" required></textarea><br>
            <button type="submit">Publier</button>
        </form>
    </details>
<?php endif; ?>

<form method="get" class="recherche-forum">
    <input type="hidden" name="id" value="<?= $id_section ?>">
    <input type="search" name="q" value="<?= htmlspecialchars($recherche) ?>"
           placeholder="Rechercher dans cette section" aria-label="Rechercher dans cette section">
    <button type="submit">Rechercher</button>
    <?php if ($recherche !== ''): ?>
        <a href="categorie.php?id=<?= $id_section ?>">Effacer</a>
    <?php endif; ?>
</form>

<?php if ($recherche !== ''): ?>
    <p><?= $total ?> discussion(s) trouvée(s) pour « <?= htmlspecialchars($recherche) ?> ».</p>
<?php endif; ?>

<?php if (empty($posts)): ?>
    <p><?= $recherche !== '' ? 'Aucune discussion ne correspond à votre recherche.' : 'Aucune discussion pour le moment.' ?></p>
<?php endif; ?>

<?php foreach ($posts as $p): ?>
    <div class="carte-forum">
        <h3><a href="post.php?id=<?= $p->getIdPost() ?>"><?= htmlspecialchars($p->getTitre()) ?></a></h3>
        <p>
            Par <?= htmlspecialchars($p->getNomAuteur()) ?> le <?= $p->getDateFormatee() ?>
            - <?= $p->getNbReponses() ?> réponse(s)
        </p>
        <p>Dernière activité : <?= $p->getDerniereActiviteFormatee() ?></p>
    </div>
<?php endforeach; ?>

<?php if ($nbPages > 1): ?>
    <nav class="pagination" aria-label="Pages de la section">
        <?php if ($page > 1): ?>
            <a href="<?= htmlspecialchars(lienPage($id_section, $recherche, $page - 1)) ?>">&larr; Précédente</a>
        <?php endif; ?>
        <span>Page <?= $page ?> sur <?= $nbPages ?></span>
        <?php if ($page < $nbPages): ?>
            <a href="<?= htmlspecialchars(lienPage($id_section, $recherche, $page + 1)) ?>">Suivante &rarr;</a>
        <?php endif; ?>
    </nav>
<?php endif; ?>

<p><a href="liste.php">Retour au forum</a></p>

<?php require __DIR__ . '/' . $racine . 'includes/footer.php'; ?>

</body>
</html>
