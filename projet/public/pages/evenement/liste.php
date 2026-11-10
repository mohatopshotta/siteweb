<?php
require __DIR__ . '/../../includes/config.php';


Session::exigerConnexion('../../');

$repo = new EvenementRepository();

$mot           = trim($_GET['q'] ?? '');
$type          = trim($_GET['type'] ?? '');
$inclurePasses = isset($_GET['passes']);
$tri           = $_GET['tri'] ?? 'date';

$evenements = $repo->rechercher($mot, $type, $inclurePasses, null, $tri);

// Pagination : 9 événements par page
const PAR_PAGE = 9;
$nbEvenements = count($evenements);
$nbPages      = max(1, (int) ceil($nbEvenements / PAR_PAGE));
$page         = min(max(1, (int) ($_GET['page'] ?? 1)), $nbPages);
$evenements   = array_slice($evenements, ($page - 1) * PAR_PAGE, PAR_PAGE);

// Garde les filtres dans les liens de pagination
$filtresUrl = array_filter(['q' => $mot, 'type' => $type, 'tri' => $tri === 'date' ? '' : $tri, 'passes' => $inclurePasses ? '1' : '']);
$types      = $repo->listerTypes();
$message    = Session::consommerFlash();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Événements</title>
    <link rel="stylesheet" href="../../assets/css/css.css">
</head>
<body>

<?php $racine = '../../'; require __DIR__ . '/../../includes/header.php'; ?>

<h1>Événements</h1>

<?php if ($message): ?>
    <p class="message-succes"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<p>
    <?php if (Session::aLeRole(['etudiant', 'medecin', 'partenaire'])): ?>
        <a href="mes_evenements.php">Mes événements</a>
    <?php endif; ?>
    <?php if (Session::aLeRole(['medecin', 'partenaire'])): ?>
        <?= Session::aLeRole(['etudiant', 'medecin', 'partenaire']) ? '|' : '' ?> <a href="creer.php">+ Créer un événement</a>
    <?php endif; ?>
</p>

<form method="get" action="liste.php" class="filtres">
    <input type="search" name="q" placeholder="Rechercher (titre, lieu, description)"
           value="<?= htmlspecialchars($mot) ?>">

    <select name="type">
        <option value="">Tous les types</option>
        <?php foreach ($types as $t): ?>
            <option value="<?= htmlspecialchars($t) ?>" <?= $t === $type ? 'selected' : '' ?>>
                <?= htmlspecialchars($t) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="tri" aria-label="Trier par">
        <option value="date"   <?= $tri === 'date'   ? 'selected' : '' ?>>Trier : date</option>
        <option value="places" <?= $tri === 'places' ? 'selected' : '' ?>>Trier : places restantes</option>
        <option value="titre"  <?= $tri === 'titre'  ? 'selected' : '' ?>>Trier : titre (A-Z)</option>
    </select>

    <label>
        <input type="checkbox" name="passes" value="1" <?= $inclurePasses ? 'checked' : '' ?>>
        Voir aussi les événements passés
    </label>

    <button type="submit">Filtrer</button>
    <a href="liste.php">Réinitialiser</a>
</form>

<?php if (empty($evenements)): ?>
    <p>Aucun événement ne correspond.</p>
<?php else: ?>
    <div class="liste-evenements">
        <?php foreach ($evenements as $evenement): ?>
            <?php
            $pris      = $evenement->getPlacesPrises();
            $total     = max(1, $evenement->getNombrePlaces());
            $pct       = min(100, (int) round($pris / $total * 100));
            $restantes = $evenement->getPlacesRestantes();
            $classeJauge = $evenement->estComplet() ? 'jauge-pleine' : ($restantes <= 5 ? 'jauge-peu' : '');
            ?>
            <div class="carte-evenement<?= $evenement->estPasse() ? ' carte-passee' : '' ?>">
                <p class="carte-type"><?= htmlspecialchars($evenement->getTypeEvenement()) ?></p>
                <h2><?= htmlspecialchars($evenement->getTitre()) ?></h2>
                <p class="carte-date"><?= htmlspecialchars($evenement->getDateAffichage()) ?></p>
                <p class="carte-lieu"><?= htmlspecialchars($evenement->getLieu()) ?></p>

                <div class="jauge <?= $classeJauge ?>" role="img"
                     aria-label="<?= $pris ?> inscrits sur <?= $evenement->getNombrePlaces() ?> places">
                    <span style="width: <?= $pct ?>%"></span>
                </div>

                <p>
                    <?php if ($evenement->estPasse()): ?>
                        <span class="badge badge-passe">Terminé</span>
                    <?php elseif ($evenement->estComplet()): ?>
                        <span class="badge badge-complet">Complet</span>
                    <?php elseif ($restantes <= 5): ?>
                        <span class="badge badge-peu">Plus que <?= $restantes ?> place(s)</span>
                    <?php else: ?>
                        <span class="badge badge-dispo"><?= $restantes ?> place(s) restante(s)</span>
                    <?php endif; ?>
                </p>
                <a href="detail.php?id=<?= $evenement->getIdEvenement() ?>">Voir le détail</a>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($nbPages > 1): ?>
        <nav class="pagination" aria-label="Pagination">
            <?php if ($page > 1): ?>
                <a href="liste.php?<?= htmlspecialchars(http_build_query($filtresUrl + ['page' => $page - 1])) ?>">&larr; Précédent</a>
            <?php endif; ?>
            <span>Page <?= $page ?> / <?= $nbPages ?></span>
            <?php if ($page < $nbPages): ?>
                <a href="liste.php?<?= htmlspecialchars(http_build_query($filtresUrl + ['page' => $page + 1])) ?>">Suivant &rarr;</a>
            <?php endif; ?>
        </nav>
    <?php endif; ?>
<?php endif; ?>

<?php require __DIR__ . '/' . $racine . 'includes/footer.php'; ?>
</body>
</html>