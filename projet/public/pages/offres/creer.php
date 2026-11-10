<?php
require __DIR__ . '/../../includes/config.php';

Session::exigerRole(['partenaire', 'gestionnaire'], '../../');

$estGestionnaire = Session::aLeRole(['gestionnaire']);
$id_entreprise = null; // reste null pour une offre du groupe

if (!$estGestionnaire) {
    $partenaire = (new UtilisateurRepository())->trouverParId(Session::getIdUtilisateur());

    if (!$partenaire instanceof NewPartenaire || !$partenaire->getIdEntreprise()) {
        die("Ton compte partenaire n'est lié à aucune entreprise.");
    }
    $id_entreprise = $partenaire->getIdEntreprise();
}

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Session::verifierCsrf();
    $erreur = NewOffre::validerFormulaire($_POST);

    if ($erreur === '') {
        $salaire = trim($_POST['salaire'] ?? '');

        $offre = new NewOffre([
            'id_auteur'     => Session::getIdUtilisateur(),
            'id_entreprise' => $id_entreprise,
            'titre'         => trim($_POST['titre']),
            'description'   => trim($_POST['description']),
            'missions'      => trim($_POST['missions']),
            'salaire'       => $salaire !== '' ? (float)$salaire : null,
            'type_offre'    => $_POST['type_offre'],
        ]);
        $id_offre = (new OffreRepository())->creer($offre);

        Session::rediriger(($estGestionnaire ? 'detail.php?id=' : 'gerer.php?id=') . $id_offre);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer une offre</title>
    <link rel="stylesheet" href="../../assets/css/css.css">
</head>
<body>

<?php $racine = '../../'; require __DIR__ . '/../../includes/header.php'; ?>

<h1><?= $estGestionnaire ? 'Créer une offre du groupe GDH' : 'Créer une offre' ?></h1>

<?php if ($erreur): ?>
    <p style="color:red;"><?= htmlspecialchars($erreur) ?></p>
<?php endif; ?>

<form method="post">
    <?= Session::champCsrf() ?>

    <label for="titre">Titre</label>
    <input type="text" id="titre" name="titre" maxlength="150" value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>" required>

    <label for="type_offre">Type</label>
    <select id="type_offre" name="type_offre">
        <?php foreach (NewOffre::TYPES as $t): ?>
            <option value="<?= $t ?>" <?= ($_POST['type_offre'] ?? '') === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
        <?php endforeach; ?>
    </select>

    <label for="description">Description</label>
    <textarea id="description" name="description" rows="5" required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>

    <label for="missions">Missions</label>
    <textarea id="missions" name="missions" rows="5" required><?= htmlspecialchars($_POST['missions'] ?? '') ?></textarea>

    <label for="salaire">Salaire en € (facultatif)</label>
    <input type="text" id="salaire" name="salaire" value="<?= htmlspecialchars($_POST['salaire'] ?? '') ?>">

    <button type="submit">Publier l'offre</button>

</form>

<?php require __DIR__ . '/' . $racine . 'includes/footer.php'; ?>
</body>
</html>