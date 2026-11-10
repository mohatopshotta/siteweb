<?php
require __DIR__ . '/../includes/config.php';

$envoye = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        (new MotDePasseOublie())->demander($email);
    }

    // Même réponse que l'email existe ou non
    $envoye = true;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mot de passe oublié</title>
</head>
<body>

<?php $racine = '../'; require __DIR__ . '/../includes/header.php'; ?>

<h1>Mot de passe oublié</h1>

<?php if ($envoye): ?>
    <p style="color:green;">Si ce compte existe, un email a été envoyé avec un lien valable 1 heure.</p>
<?php endif; ?>

<form method="post">
    <?= Session::champCsrf() ?>
    <label>Email</label>
    <input type="email" name="email" required><br>

    <button type="submit">Envoyer le lien</button>
</form>

<p><a href="connexion.php">Retour à la connexion</a></p>

</body>
</html>
