<?php
require __DIR__ . '/../../includes/config.php';

Session::exigerRole(['medecin', 'partenaire'], '../../');

$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $erreurs = NewEvenement::validerFormulaire($_POST);

    if (empty($erreurs)) {
        try {
            $evenement = NewEvenement::depuisFormulaire($_POST);
            $id_evenement = (new EvenementRepository())->creer($evenement, Session::getIdUtilisateur());
            Session::rediriger('detail.php?id=' . $id_evenement);
        } catch (PDOException $e) {
            error_log('Création événement : ' . $e->getMessage());
            $erreurs[] = "L'événement n'a pas pu être enregistré. Réessaie plus tard.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer un événement</title>
    <link rel="stylesheet" href="../../assets/css/css.css">
</head>
<body>

<?php $racine = '../../'; require __DIR__ . '/../../includes/header.php'; ?>

<h1>Créer un événement</h1>

<?php if (!empty($erreurs)): ?>
    <ul class="erreurs">
        <?php foreach ($erreurs as $erreur): ?>
            <li><?= htmlspecialchars($erreur) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post" action="creer.php">
    <?= Session::champCsrf() ?>

    <label for="type_evenement">Type d'événement</label>
    <input type="text" id="type_evenement" name="type_evenement"
           value="<?= htmlspecialchars($_POST['type_evenement'] ?? '') ?>" required>

    <label for="titre">Titre</label>
    <input type="text" id="titre" name="titre"
           value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>" required>

    <label for="description">Description</label>
    <textarea id="description" name="description" rows="5" required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>

    <label for="lieu">Lieu</label>
    <input type="text" id="lieu" name="lieu"
           value="<?= htmlspecialchars($_POST['lieu'] ?? '') ?>" required>

    <label for="elements_requis">Éléments requis (facultatif)</label>
    <textarea id="elements_requis" name="elements_requis" rows="3"><?= htmlspecialchars($_POST['elements_requis'] ?? '') ?></textarea>

    <label for="nombre_places">Nombre de places</label>
    <input type="number" id="nombre_places" name="nombre_places" min="1"
           value="<?= htmlspecialchars($_POST['nombre_places'] ?? '') ?>" required>

    <button type="submit">Créer l'événement</button>

</form>

</body>
</html>