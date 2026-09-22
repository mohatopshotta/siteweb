<?php
require __DIR__ . '/../includes/config.php';

$erreurs = [];

$hopitaux = $pdo->query('SELECT id_hopital, nom FROM hopital ORDER BY nom')->fetchAll();
$specialites = $pdo->query('SELECT id_specialite, libelle FROM specialite ORDER BY libelle')->fetchAll();
$etablissements = $pdo->query('SELECT id_etablissement, nom FROM etablissement ORDER BY nom')->fetchAll();
$entreprises = $pdo->query('SELECT id_entreprise, nom FROM entreprise ORDER BY nom')->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
</head>
<body>

<h1>Inscription</h1>

<?php foreach ($erreurs as $erreur): ?>
    <p style="color:red;"><?= htmlspecialchars($erreur) ?></p>
<?php endforeach; ?>

<form method="post" enctype="multipart/form-data">

    <label>Nom</label>
    <input type="text" name="nom" value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" required><br>

    <label>Prénom</label>
    <input type="text" name="prenom" value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>" required><br>

    <label>Email</label>
    <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required><br>

    <label>Mot de passe</label>
    <input type="password" name="mot_de_passe" required><br>

    <label>Je suis</label>
    <select name="role" id="role" onchange="afficherChamps()" required>
        <option value="">-- choisir --</option>
        <option value="etudiant">Étudiant</option>
        <option value="medecin">Médecin</option>
        <option value="partenaire">Partenaire</option>
    </select><br>

    <button type="submit">S'inscrire</button>
</form>

<p>Déjà inscrit ? <a href="connexion.php">Se connecter</a></p>

</body>
</html>