<?php
require __DIR__ . '/../../includes/config.php';

Session::exigerConnexion('../../');

$manager = new EvenementManager();
$id_utilisateur = Session::getIdUtilisateur();
$id_evenement = (int)($_GET['id'] ?? 0);

$evenement = $manager->trouverParId($id_evenement);
if ($evenement === null) {
    die("Événement introuvable.");
}

if (!$manager->estOrganisateur($id_utilisateur, $id_evenement)) {
    die("Accès réservé aux organisateurs de cet événement.");
}

$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modifier'])) {
    $erreurs = Evenement::validerFormulaire($_POST);

    if (empty($erreurs)) {
        $manager->modifier(Evenement::depuisFormulaire($_POST, $id_evenement));
        Session::rediriger('gerer.php?id=' . $id_evenement);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['retirer_id'])) {
    $manager->retirerParticipant((int)$_POST['retirer_id'], $id_evenement);
    Session::rediriger('gerer.php?id=' . $id_evenement);
}

$inscrits = $manager->listerInscrits($id_evenement);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gérer — <?= htmlspecialchars($evenement->getTitre()) ?></title>
    <link rel="stylesheet" href="../../assets/css/css.css">
</head>
<body>

<?php $racine = '../../'; require __DIR__ . '/../../includes/header.php'; ?>

<a href="detail.php?id=<?= $id_evenement ?>">&larr; Retour à l'événement</a>

<h1>Gérer : <?= htmlspecialchars($evenement->getTitre()) ?></h1>

<?php if (!empty($erreurs)): ?>
    <ul class="erreurs">
        <?php foreach ($erreurs as $erreur): ?>
            <li><?= htmlspecialchars($erreur) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<h2>Modifier la fiche</h2>
<form method="post" action="gerer.php?id=<?= $id_evenement ?>">

    <label for="type_evenement">Type d'événement</label>
    <input type="text" id="type_evenement" name="type_evenement"
           value="<?= htmlspecialchars($evenement->getTypeEvenement()) ?>" required>

    <label for="titre">Titre</label>
    <input type="text" id="titre" name="titre"
           value="<?= htmlspecialchars($evenement->getTitre()) ?>" required>

    <label for="description">Description</label>
    <textarea id="description" name="description" rows="5" required><?= htmlspecialchars($evenement->getDescription()) ?></textarea>

    <label for="lieu">Lieu</label>
    <input type="text" id="lieu" name="lieu"
           value="<?= htmlspecialchars($evenement->getLieu()) ?>" required>

    <label for="elements_requis">Éléments requis (facultatif)</label>
    <textarea id="elements_requis" name="elements_requis" rows="3"><?= htmlspecialchars($evenement->getElementsRequis() ?? '') ?></textarea>

    <label for="nombre_places">Nombre de places</label>
    <input type="number" id="nombre_places" name="nombre_places" min="1"
           value="<?= $evenement->getNombrePlaces() ?>" required>

    <button type="submit" name="modifier">Enregistrer les modifications</button>
</form>

<h2>Inscrits (<?= count($inscrits) ?>)</h2>

<table>
    <thead>
    <tr>
        <th>Nom</th>
        <th>Email</th>
        <th>Rôle</th>
        <th>Action</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($inscrits as $personne): ?>
        <tr>
            <td><?= htmlspecialchars($personne->getNomComplet()) ?></td>
            <td><?= htmlspecialchars($personne->getEmail()) ?></td>
            <td><?= $personne->estOrganisateur() ? 'Organisateur' : 'Participant' ?></td>
            <td>
                <?php if (!$personne->estOrganisateur()): ?>
                    <form method="post" action="gerer.php?id=<?= $id_evenement ?>" style="display:inline">
                        <input type="hidden" name="retirer_id" value="<?= $personne->getIdUtilisateur() ?>">
                        <button type="submit" onclick="return confirm('Retirer cette personne ?');">Retirer</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>