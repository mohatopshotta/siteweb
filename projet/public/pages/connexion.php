<?php
require __DIR__ . '/../includes/config.php';

// message d'erreur affiché si connexion échoue
$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';

    // on cherche l'utilisateur par son email
    $req = $pdo->prepare('SELECT * FROM utilisateur WHERE email = ?');
    $req->execute([$email]);
    $utilisateur = $req->fetch();

    if (!$utilisateur || !password_verify($mot_de_passe, $utilisateur['mot_de_passe'])) {
        // email inexistant ou mot de passe incorrect
        $erreur = 'Email ou mot de passe incorrect.';
    } elseif (!$utilisateur['valide']) {
        // compte pas encore validé par un gestionnaire
        $erreur = 'Ton compte est en attente de validation par un gestionnaire.';
    } else {
        // on cherche dans quelle table (etudiant/medecin/partenaire/gestionnaire) est cet utilisateur
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

        // on ouvre la session
        $_SESSION['id_utilisateur'] = $utilisateur['id_utilisateur'];
        $_SESSION['nom'] = $utilisateur['nom'];
        $_SESSION['prenom'] = $utilisateur['prenom'];
        $_SESSION['role'] = $role;

        // redirection vers l'accueil une fois connecté
        header('Location: ../accueil/accueil.php');
        exit;
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

<?php require __DIR__ . '/../includes/header.php'; ?>

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