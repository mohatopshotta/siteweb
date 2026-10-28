<?php
require __DIR__ . '/../includes/config.php';

$jeton = $_GET['jeton'] ?? $_POST['jeton'] ?? '';
if (!is_string($jeton)) {
    $jeton = '';
}

$service = new MotDePasseOublie();
$lienValide = $service->jetonValide($jeton);
$erreurs = [];
$termine = false;

if ($lienValide && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $motDePasse = $_POST['mot_de_passe'] ?? '';
    $confirmation = $_POST['confirmation'] ?? '';

    if ($motDePasse !== $confirmation) {
        $erreurs[] = 'Les deux mots de passe sont différents.';
    } else {
        $erreurs = $service->reinitialiser($jeton, $motDePasse);
        $termine = empty($erreurs);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nouveau mot de passe</title>
</head>
<body>

<?php $racine = '../'; require __DIR__ . '/../includes/header.php'; ?>

<h1>Nouveau mot de passe</h1>

<?php if ($termine): ?>
    <p style="color:green;">Mot de passe modifié. <a href="connexion.php">Se connecter</a></p>

<?php elseif (!$lienValide): ?>
    <p style="color:red;">Lien invalide ou expiré.</p>
    <p><a href="mot_de_passe_oublie.php">Demander un nouveau lien</a></p>

<?php else: ?>
    <?php foreach ($erreurs as $erreur): ?>
        <p style="color:red;"><?= htmlspecialchars($erreur) ?></p>
    <?php endforeach; ?>

    <form method="post">
        <?= Session::champCsrf() ?>
        <input type="hidden" name="jeton" value="<?= htmlspecialchars($jeton) ?>">

        <label>Nouveau mot de passe</label>
        <input type="password" name="mot_de_passe" required><br>

        <label>Confirmer le mot de passe</label>
        <input type="password" name="confirmation" required><br>

        <button type="submit">Changer mon mot de passe</button>
    </form>
<?php endif; ?>

</body>
</html>
