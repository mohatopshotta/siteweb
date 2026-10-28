<?php
require __DIR__ . '/../includes/config.php';

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';

    $limite = new TentativeConnexionRepository();

    if ($limite->tropDEchecs($email)) {
        $erreur = 'Trop de tentatives de connexion. Réessaie dans ' . TentativeConnexionRepository::FENETRE_MINUTES . ' minutes.';
    } else {
        $utilisateurRepository = new UtilisateurRepository();
        $utilisateur = $utilisateurRepository->trouverParEmail($email);

        if ($utilisateur === null || !$utilisateur->verifierMotDePasse($mot_de_passe)) {
            $erreur = 'Email ou mot de passe incorrect.';
            $limite->enregistrerEchec($email);
        } elseif (!$utilisateur->isValide()) {
            $erreur = 'Ton compte est en attente de validation par un gestionnaire.';
        } else {
            $limite->effacer($email);
            Session::connecter($utilisateur);
            $utilisateurRepository->majDerniereConnexion($utilisateur->getIdUtilisateur());
            Session::rediriger('../accueil/accueil.php');
        }
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
    <?= Session::champCsrf() ?>
    <label>Email</label>
    <input type="email" name="email" required><br>

    <label>Mot de passe</label>
    <input type="password" name="mot_de_passe" required><br>

    <button type="submit">Se connecter</button>
</form>

<p><a href="mot_de_passe_oublie.php">Mot de passe oublié ?</a></p>

<p>Pas encore de compte ? <a href="inscription.php">S'inscrire</a></p>

<?php require __DIR__ . '/' . $racine . 'includes/footer.php'; ?>
</body>
</html>