<?php
require __DIR__ . '/../includes/config.php';

Session::exigerConnexion('../');

$manager = new UtilisateurRepository();
$id_utilisateur = Session::getIdUtilisateur();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');

    if ($nom !== '' && $prenom !== '') {
        $manager->modifierNomPrenom($id_utilisateur, $nom, $prenom);

        Session::majIdentite($nom, $prenom);
        $message = 'Profil mis à jour.';
    }
}

$utilisateur = $manager->trouverParId($id_utilisateur);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon profil</title>
</head>
<body>

<?php $racine = '../'; require __DIR__ . '/../includes/header.php'; ?>

<h1>Mon profil</h1>

<?php if ($message): ?>
    <p style="color:green;"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<form method="post">
    <?= Session::champCsrf() ?>
    <label>Nom</label>
    <input type="text" name="nom" value="<?= htmlspecialchars($utilisateur->getNom()) ?>"><br>

    <label>Prénom</label>
    <input type="text" name="prenom" value="<?= htmlspecialchars($utilisateur->getPrenom()) ?>"><br>

    <label>Email</label>
    <input type="text" value="<?= htmlspecialchars($utilisateur->getEmail()) ?>" disabled><br>

    <label>Rôle</label>
    <input type="text" value="<?= htmlspecialchars($utilisateur->getLibelleRole()) ?>" disabled><br>

    <?php if ($utilisateur instanceof NewEtudiant): ?>
        <p>Formation : <?= htmlspecialchars($utilisateur->getFormation()) ?></p>
        <p>Établissement : <?= htmlspecialchars($utilisateur->getNomEtablissement() ?? '') ?></p>
    <?php elseif ($utilisateur instanceof NewMedecin): ?>
        <p>Spécialité : <?= htmlspecialchars($utilisateur->getSpecialite() ?? '') ?></p>
        <p>Hôpital : <?= htmlspecialchars($utilisateur->getHopital() ?? '') ?></p>
        <p>Établissement d'enseignement : <?= htmlspecialchars($utilisateur->getNomEtablissement() ?? '—') ?></p>
    <?php elseif ($utilisateur instanceof NewPartenaire): ?>
        <p>Poste : <?= htmlspecialchars($utilisateur->getPoste()) ?></p>
        <p>Entreprise : <?= htmlspecialchars($utilisateur->getNomEntreprise() ?? '') ?></p>
    <?php endif; ?>

    <button type="submit">Enregistrer</button>
</form>

<p><a href="deconnexion.php">Se déconnecter</a></p>

</body>
</html>