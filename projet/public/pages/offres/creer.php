<?php
// config.php démarre la session et crée la connexion $pdo
require '../../includes/config.php';

// Profil de l'utilisateur ('profil' ou 'role' selon les pages, on accepte les deux)
$profil = $_SESSION['profil'] ?? $_SESSION['role'] ?? '';

// Seuls les partenaires connectés ont le droit d'être ici
if (!isset($_SESSION['id_utilisateur']) || $profil !== 'partenaire') {
    header('Location: ../connexion.php');
    exit;
}

$id_utilisateur = $_SESSION['id_utilisateur'];

// On cherche l'entreprise du partenaire (table partenaire)
$stmt = $pdo->prepare("SELECT id_entreprise FROM partenaire WHERE id_utilisateur = ?");
$stmt->execute([$id_utilisateur]);
$id_entreprise = $stmt->fetchColumn();

if (!$id_entreprise) {
    die("Ton compte partenaire n'est lié à aucune entreprise.");
}

$erreur = '';

// Ce bloc s'exécute quand le formulaire est envoyé
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $titre       = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $missions    = trim($_POST['missions'] ?? '');
    $type_offre  = $_POST['type_offre'] ?? '';
    $salaire     = trim($_POST['salaire'] ?? '');

    // Vérifications
    if ($titre === '' || $description === '' || $missions === '') {
        $erreur = "Le titre, la description et les missions sont obligatoires.";
    } elseif (mb_strlen($titre) > 150) {
        $erreur = "Le titre est trop long (150 caractères maximum).";
    } elseif (!in_array($type_offre, ['stage', 'alternance', 'cdd', 'cdi'])) {
        $erreur = "Type d'offre invalide.";
    } elseif ($salaire !== '' && !is_numeric($salaire)) {
        $erreur = "Le salaire doit être un nombre.";
    } else {
        // Tout est bon : on enregistre l'offre (elle est "ouverte" par défaut)
        $stmt = $pdo->prepare(
            "INSERT INTO offre (id_auteur, id_entreprise, titre, description, missions, salaire, type_offre)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $id_utilisateur,
            $id_entreprise,
            $titre,
            $description,
            $missions,
            $salaire !== '' ? $salaire : null,   // salaire vide = NULL
            $type_offre,
        ]);

        // On va directement sur la page de gestion de la nouvelle offre
        header('Location: gerer.php?id=' . $pdo->lastInsertId());
        exit;
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

<?php $racine = '../../'; require '../../includes/header.php'; ?>

<h1>Créer une offre</h1>

<?php if ($erreur): ?>
    <p style="color:red;"><?= htmlspecialchars($erreur) ?></p>
<?php endif; ?>

<form method="post">

    <label>Titre</label>
    <input type="text" name="titre" maxlength="150" value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>" required>

    <label>Type</label>
    <select name="type_offre">
        <option value="stage">Stage</option>
        <option value="alternance">Alternance</option>
        <option value="cdd">CDD</option>
        <option value="cdi">CDI</option>
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