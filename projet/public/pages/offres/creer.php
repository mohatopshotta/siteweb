<?php
require __DIR__ . '/../../includes/config.php';

// Seuls les partenaires connectés ont le droit d'être ici
Session::exigerRole(['partenaire'], '../../');

// le partenaire connecté (objet Partenaire) : on a besoin de son entreprise
$partenaire = (new UtilisateurManager())->trouverParId(Session::getIdUtilisateur());

if (!$partenaire instanceof Partenaire || !$partenaire->getIdEntreprise()) {
    die("Ton compte partenaire n'est lié à aucune entreprise.");
}

$erreur = '';

// Ce bloc s'exécute quand le formulaire est envoyé
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Vérifications (méthode de la classe Offre)
    $erreur = Offre::validerFormulaire($_POST);

    if ($erreur === '') {
        $salaire = trim($_POST['salaire'] ?? '');

        // Tout est bon : on enregistre l'offre (elle est "ouverte" par défaut)
        $offre = new Offre([
            'id_auteur'     => $partenaire->getIdUtilisateur(),
            'id_entreprise' => $partenaire->getIdEntreprise(),
            'titre'         => trim($_POST['titre']),
            'description'   => trim($_POST['description']),
            'missions'      => trim($_POST['missions']),
            'salaire'       => $salaire !== '' ? (float)$salaire : null,   // salaire vide = NULL
            'type_offre'    => $_POST['type_offre'],
        ]);
        $id_offre = (new OffreManager())->creer($offre);

        // On va directement sur la page de gestion de la nouvelle offre
        Session::rediriger('gerer.php?id=' . $id_offre);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer une offre</title>
    <link rel="stylesheet" href="../../assets/css/css.css">
</head>
<body>

<?php $racine = '../../'; require __DIR__ . '/../../includes/header.php'; ?>

<h1>Créer une offre</h1>

<?php if ($erreur): ?>
    <p style="color:red;"><?= htmlspecialchars($erreur) ?></p>
<?php endif; ?>

<form method="post">

    <label>Titre</label>
    <input type="text" name="titre" maxlength="150" value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>" required>

    <label>Type</label>
    <select name="type_offre">
        <?php foreach (Offre::TYPES as $t): ?>
            <option value="<?= $t ?>" <?= ($_POST['type_offre'] ?? '') === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
        <?php endforeach; ?>
    </select>

    <label>Description</label>
    <textarea name="description" rows="5" required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>

    <label>Missions</label>
    <textarea name="missions" rows="5" required><?= htmlspecialchars($_POST['missions'] ?? '') ?></textarea>

    <label>Salaire en € (facultatif)</label>
    <input type="text" name="salaire" value="<?= htmlspecialchars($_POST['salaire'] ?? '') ?>">

    <button type="submit">Publier l'offre</button>

</form>

</body>
</html>