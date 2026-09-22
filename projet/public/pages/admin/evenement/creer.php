<?php


session_start();
require '../../includes/config.php';

// ------------------------------------------------------------
// Accès réservé aux médecins et partenaires
// ------------------------------------------------------------
if (!isset($_SESSION['id_utilisateur']) || !in_array($_SESSION['profil'], ['medecin', 'partenaire'])) {
    header('Location: ../connexion.php');
    exit;
}

$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Récupération des champs
    $type_evenement  = trim($_POST['type_evenement'] ?? '');
    $titre           = trim($_POST['titre'] ?? '');
    $description     = trim($_POST['description'] ?? '');
    $lieu            = trim($_POST['lieu'] ?? '');
    $elements_requis = trim($_POST['elements_requis'] ?? '');
    $nombre_places   = $_POST['nombre_places'] ?? '';

    // Validation basique
    if ($type_evenement === '') $erreurs[] = "Le type d'événement est obligatoire.";
    if ($titre === '') $erreurs[] = "Le titre est obligatoire.";
    if ($description === '') $erreurs[] = "La description est obligatoire.";
    if ($lieu === '') $erreurs[] = "Le lieu est obligatoire.";
    if (!ctype_digit((string)$nombre_places) || (int)$nombre_places < 1) {
        $erreurs[] = "Le nombre de places doit être un entier positif.";
    }

    // Si tout est bon, on enregistre
    if (empty($erreurs)) {
        try {
            $pdo->beginTransaction();

            // 1. Création de l'événement
            $stmt = $pdo->prepare(
                "INSERT INTO evenement (type_evenement, titre, description, lieu, elements_requis, nombre_places)
                 VALUES (:type_evenement, :titre, :description, :lieu, :elements_requis, :nombre_places)"
            );
            $stmt->execute([
                ':type_evenement'  => $type_evenement,
                ':titre'           => $titre,
                ':description'     => $description,
                ':lieu'            => $lieu,
                ':elements_requis' => $elements_requis !== '' ? $elements_requis : null,
                ':nombre_places'   => (int)$nombre_places,
            ]);

            $id_evenement = $pdo->lastInsertId();

            // 2. Le créateur devient organisateur de l'événement
            $stmt = $pdo->prepare(
                "INSERT INTO inscription_evenement (id_utilisateur, id_evenement, organiser)
                 VALUES (:id_utilisateur, :id_evenement, 1)"
            );
            $stmt->execute([
                ':id_utilisateur' => $_SESSION['id_utilisateur'],
                ':id_evenement'   => $id_evenement,
            ]);

            $pdo->commit();

            header('Location: detail.php?id=' . $id_evenement);
            exit;

        } catch (PDOException $e) {
            $pdo->rollBack();
            $erreurs[] = "Erreur lors de l'enregistrement : " . $e->getMessage();
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

<h1>Créer un événement</h1>

<?php if (!empty($erreurs)): ?>
    <ul class="erreurs">
        <?php foreach ($erreurs as $erreur): ?>
            <li><?= htmlspecialchars($erreur) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post" action="creer.php">

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