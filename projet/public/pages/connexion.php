<?php
require __DIR__ . '/../includes/config.php';

$erreur = '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
</head>
<body>

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
<?php
require __DIR__ . '/../includes/config.php';

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';

    $req = $pdo->prepare('SELECT * FROM utilisateur WHERE email = ?');
    $req->execute([$email]);
    $utilisateur = $req->fetch();

    if (!$utilisateur || !password_verify($mot_de_passe, $utilisateur['mot_de_passe'])) {
        $erreur = 'Email ou mot de passe incorrect.';
    } elseif (!$utilisateur['valide']) {
        $erreur = 'Ton compte est en attente de validation par un gestionnaire.';
    } else {
        $req = $pdo->prepare("
            SELECT 'etudiant' AS role FROM etudiant WHERE id_utilisateur = ?
            UNION SELECT 'medecin' FROM medecin WHERE id_utilisateur = ?
            UNION SELECT 'partenaire' FROM partenaire WHERE id_utilisateur = ?
            UNION SELECT 'gestionnaire' FROM gestionnaire WHERE id_utilisateur = ?
        ");
        $req->execute([
            $utilisateur['id_utilisateur'],
            $utilisateur['id_utilisateur'],
            $utilisateur['id_utilisateur'],
            $utilisateur['id_utilisateur'],
        ]);
        $role = $req->fetchColumn();

        $_SESSION['id_utilisateur'] = $utilisateur['id_utilisateur'];
        $_SESSION['nom'] = $utilisateur['nom'];
        $_SESSION['prenom'] = $utilisateur['prenom'];
        $_SESSION['role'] = $role;

        header('Location: ../accueil/accueil.php');
        exit;
    }
}
?>