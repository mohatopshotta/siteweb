<?php
require __DIR__ . '/../includes/config.php';

// message d'erreur affiché si la connexion échoue
$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';

    // on cherche l'utilisateur par son email (objet Etudiant, Medecin, Partenaire ou Gestionnaire)
    $utilisateur = (new UtilisateurManager())->trouverParEmail($email);

    if ($utilisateur === null || !$utilisateur->verifierMotDePasse($mot_de_passe)) {
        // email inexistant ou mot de passe incorrect
        $erreur = 'Email ou mot de passe incorrect.';
    } elseif (!$utilisateur->isValide()) {
        // compte pas encore validé par un gestionnaire
        $erreur = 'Ton compte est en attente de validation par un gestionnaire.';
    } else {
        // on ouvre la session puis on redirige vers l'accueil
        Session::connecter($utilisateur);
        Session::rediriger('../accueil/accueil.php');
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
</head>
<body>

<?php $racine = '../'; require __DIR__ . '/../includes/header.php'; ?>

<h1>Connexion</h1>

<?php if (isset($_GET['inscription'])): ?>
    <p style="color:green;">Inscription enregistrée, en attente de validation par un gestionnaire.</p>
<?php endif; ?>

<?php if ($erreur): ?>
    <p style="color:red;"><?= htmlspecialchars($erreur) ?></p>
<?php endif; ?>

<form method="post">
    <label>Email</label>
    <input type="email" name="email" required><br>

    <label>Mot de passe</label>
    <input type="password" name="mot_de_passe" required><br>

    <button type="submit">Se connecter</button>
</form>

<p>Pas encore de compte ? <a href="inscription.php">S'inscrire</a></p>

</body>
</html>