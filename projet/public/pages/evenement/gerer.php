<?php
session_start();
require '../../includes/config.php';

if (!isset($_SESSION['id_utilisateur'])) {
    header('Location: ../connexion.php');
    exit;
}

$id_evenement = $_GET['id'] ?? null;
if (!$id_evenement || !ctype_digit((string)$id_evenement)) {
    die("Événement introuvable.");
}

$id_utilisateur = $_SESSION['id_utilisateur'];

// ------------------------------------------------------------
// Vérifier que l'utilisateur est bien organisateur de cet événement
// ------------------------------------------------------------
$stmt = $pdo->prepare(
    "SELECT organiser FROM inscription_evenement
     WHERE id_utilisateur = :id_utilisateur AND id_evenement = :id_evenement"
);
$stmt->execute([':id_utilisateur' => $id_utilisateur, ':id_evenement' => $id_evenement]);
$inscription = $stmt->fetch();

if (!$inscription || (int)$inscription['organiser'] !== 1) {
    die("Accès réservé aux organisateurs de cet événement.");
}

// ------------------------------------------------------------
// Récupération de l'événement
// ------------------------------------------------------------
$stmt = $pdo->prepare("SELECT * FROM evenement WHERE id_evenement = :id");
$stmt->execute([':id' => $id_evenement]);
$evenement = $stmt->fetch();

if (!$evenement) {
    die("Événement introuvable.");
}

$erreurs = [];
$message = '';

// ------------------------------------------------------------
// Modification de la fiche événement
// ------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modifier'])) {

    $type_evenement  = trim($_POST['type_evenement'] ?? '');
    $titre           = trim($_POST['titre'] ?? '');
    $description     = trim($_POST['description'] ?? '');
    $lieu            = trim($_POST['lieu'] ?? '');
    $elements_requis = trim($_POST['elements_requis'] ?? '');
    $nombre_places   = $_POST['nombre_places'] ?? '';

    if ($type_evenement === '') $erreurs[] = "Le type d'événement est obligatoire.";
    if ($titre === '') $erreurs[] = "Le titre est obligatoire.";
    if ($description === '') $erreurs[] = "La description est obligatoire.";
    if ($lieu === '') $erreurs[] = "Le lieu est obligatoire.";
    if (!ctype_digit((string)$nombre_places) || (int)$nombre_places < 1) {
        $erreurs[] = "Le nombre de places doit être un entier positif.";
    }

    if (empty($erreurs)) {
        $stmt = $pdo->prepare(
            "UPDATE evenement
             SET type_evenement = :type_evenement, titre = :titre, description = :description,
                 lieu = :lieu, elements_requis = :elements_requis, nombre_places = :nombre_places
             WHERE id_evenement = :id"
        );
        $stmt->execute([
            ':type_evenement'  => $type_evenement,
            ':titre'           => $titre,
            ':description'     => $description,
            ':lieu'            => $lieu,
            ':elements_requis' => $elements_requis !== '' ? $elements_requis : null,
            ':nombre_places'   => (int)$nombre_places,
            ':id'              => $id_evenement,
        ]);

        header('Location: gerer.php?id=' . $id_evenement);
        exit;
    }
}

// ------------------------------------------------------------
// Retirer un inscrit (refus d'inscription)
// ------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['retirer_id'])) {

    $retirer_id = $_POST['retirer_id'];

    // un organisateur ne peut pas se retirer lui-même via ce bouton
    if ((int)$retirer_id !== (int)$id_utilisateur) {
        $stmt = $pdo->prepare(
            "DELETE FROM inscription_evenement
             WHERE id_utilisateur = :id_utilisateur AND id_evenement = :id_evenement AND organiser = 0"
        );
        $stmt->execute([
            ':id_utilisateur' => $retirer_id,
            ':id_evenement'   => $id_evenement,
        ]);
    }

    header('Location: gerer.php?id=' . $id_evenement);
    exit;
}

// Recharger l'événement au cas où il vient d'être modifié
$stmt = $pdo->prepare("SELECT * FROM evenement WHERE id_evenement = :id");
$stmt->execute([':id' => $id_evenement]);
$evenement = $stmt->fetch();

// ------------------------------------------------------------
// Liste des inscrits (organisateurs et participants)
// ------------------------------------------------------------
$stmt = $pdo->prepare(
    "SELECT u.id_utilisateur, u.nom, u.prenom, u.email, ie.organiser
     FROM inscription_evenement ie
     JOIN utilisateur u ON u.id_utilisateur = ie.id_utilisateur
     WHERE ie.id_evenement = :id
     ORDER BY ie.organiser DESC, u.nom"
);
$stmt->execute([':id' => $id_evenement]);
$inscrits = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gérer — <?= htmlspecialchars($evenement['titre']) ?></title>
    <link rel="stylesheet" href="../../assets/css/css.css">
</head>
<body>

<a href="detail.php?id=<?= $id_evenement ?>">&larr; Retour à l'événement</a>

<h1>Gérer : <?= htmlspecialchars($evenement['titre']) ?></h1>

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
           value="<?= htmlspecialchars($evenement['type_evenement']) ?>" required>

    <label for="titre">Titre</label>
    <input type="text" id="titre" name="titre"
           value="<?= htmlspecialchars($evenement['titre']) ?>" required>

    <label for="description">Description</label>
    <textarea id="description" name="description" rows="5" required><?= htmlspecialchars($evenement['description']) ?></textarea>

    <label for="lieu">Lieu</label>
    <input type="text" id="lieu" name="lieu"
           value="<?= htmlspecialchars($evenement['lieu']) ?>" required>

    <label for="elements_requis">Éléments requis (facultatif)</label>
    <textarea id="elements_requis" name="elements_requis" rows="3"><?= htmlspecialchars($evenement['elements_requis'] ?? '') ?></textarea>

    <label for="nombre_places">Nombre de places</label>
    <input type="number" id="nombre_places" name="nombre_places" min="1"
           value="<?= htmlspecialchars($evenement['nombre_places']) ?>" required>

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
            <td><?= htmlspecialchars($personne['prenom'] . ' ' . $personne['nom']) ?></td>
            <td><?= htmlspecialchars($personne['email']) ?></td>
            <td><?= (int)$personne['organiser'] === 1 ? 'Organisateur' : 'Participant' ?></td>
            <td>
                <?php if ((int)$personne['organiser'] !== 1): ?>
                    <form method="post" action="gerer.php?id=<?= $id_evenement ?>" style="display:inline">
                        <input type="hidden" name="retirer_id" value="<?= $personne['id_utilisateur'] ?>">
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