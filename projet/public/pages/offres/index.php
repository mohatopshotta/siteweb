<?php
require_once '../../includes/config.php';
/** @var PDO $pdo */

$racine = '../../';

$type = $_GET['type'] ?? '';
$erreur = '';

$sql = "SELECT o.id_offre, o.titre, o.type_offre, o.salaire,
               o.date_publication, COALESCE(e.nom, 'Groupe GDH') AS entreprise
        FROM offre o
        LEFT JOIN entreprise e ON e.id_entreprise = o.id_entreprise
        WHERE o.etat = 'ouvert'";

$params = [];

if (in_array($type, ['stage', 'alternance', 'cdd', 'cdi'], true)) {
    $sql .= " AND o.type_offre = ?";
    $params[] = $type;
}

$sql .= " ORDER BY o.date_publication DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$offres = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Choix d'une offre dans la liste : on redirige vers sa fiche détaillée,
// où se trouve le vrai formulaire de candidature (avec lettre de motivation).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_offre = filter_input(INPUT_POST, 'id_offre', FILTER_VALIDATE_INT);

    if (!$id_offre) {
        $erreur = "Veuillez sélectionner une offre avant de continuer.";
    } else {
        header("Location: detail.php?id=$id_offre");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nos offres d'emploi</title>
    <link rel="stylesheet" href="<?= $racine ?>assets/css/css.css">
</head>

<body>
<?php require $racine . 'includes/header.php'; ?>

<div class="container">

    <header class="entete">
        <h1>Découvrez toutes nos offres !</h1>
        <p>
            Vous êtes à la recherche d'un stage, d'une alternance
            ou d'un emploi dans nos établissements partenaires ? Consultez toutes nos opportunités et
            choisissez l'offre qui correspond à votre projet professionnel.
        </p>
        <p>Votre prochaine opportunité commence ici !</p>
    </header>

    <?php if ($erreur): ?>
        <p style="color:red"><?= htmlspecialchars($erreur) ?></p>
    <?php endif; ?>

    <section class="filtre">
        <h2>Rechercher une offre</h2>

        <form method="get">
            <select name="type" onchange="this.form.submit()">
                <option value="">Tous les types de contrats</option>

                <?php foreach (['stage', 'alternance', 'cdd', 'cdi'] as $t): ?>
                    <option
                            value="<?= htmlspecialchars($t) ?>"
                        <?= $type === $t ? 'selected' : '' ?>
                    >
                        <?= strtoupper($t) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </section>

    <h2>Nos offres disponibles</h2>
    <p><?= count($offres) ?> offre(s) disponible(s)</p>

    <?php if (empty($offres)): ?>

        <div class="vide">
            <h3>Aucune offre disponible</h3>
            <p>De nouvelles opportunités seront bientôt proposées.</p>
        </div>

    <?php else: ?>

        <form method="post">
            <div class="offres">

                <?php foreach ($offres as $o): ?>
                    <article class="offre">

                        <h2>
                            <a href="detail.php?id=<?= (int) $o['id_offre'] ?>">
                                <?= htmlspecialchars($o['titre']) ?>
                            </a>
                        </h2>

                        <p class="entreprise">
                            <?= htmlspecialchars($o['entreprise']) ?>
                        </p>

                        <span class="badge">
                            <?= strtoupper(htmlspecialchars($o['type_offre'])) ?>
                        </span>

                        <?php if ($o['salaire'] !== null && $o['salaire'] !== ''): ?>
                            <p>
                                Salaire :
                                <?= htmlspecialchars((string) $o['salaire']) ?> €
                            </p>
                        <?php endif; ?>

                        <p>
                            Publiée le
                            <?= date('d/m/Y', strtotime($o['date_publication'])) ?>
                        </p>

                        <label>
                            <input
                                    type="radio"
                                    name="id_offre"
                                    value="<?= (int) $o['id_offre'] ?>"
                                    required
                            >
                            Je choisis cette offre
                        </label>

                        <a href="detail.php?id=<?= (int) $o['id_offre'] ?>">
                            Voir les détails
                        </a>

                    </article>
                <?php endforeach; ?>

            </div>

            <button type="submit">
                Postuler à l'offre sélectionnée
            </button>
        </form>

    <?php endif; ?>

</div>
</body>
</html>