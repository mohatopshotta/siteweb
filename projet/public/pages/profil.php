<?php
require __DIR__ . '/../includes/config.php';

Session::exigerConnexion('../');

$manager = new UtilisateurRepository();
$id_utilisateur = Session::getIdUtilisateur();
$utilisateur = $manager->trouverParId($id_utilisateur);

$message = '';
$erreur_suppression = '';
$compte_supprime = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'modifier';

    if ($action === 'supprimer') {
        // RGPD : droit à l'effacement. On redemande le mot de passe pour être sûr que c'est bien la personne.
        if ($utilisateur instanceof NewGestionnaire) {
            $erreur_suppression = 'Un compte gestionnaire ne peut pas être supprimé depuis cette page.';
        } elseif (!$utilisateur->verifierMotDePasse($_POST['mot_de_passe'] ?? '')) {
            $erreur_suppression = 'Mot de passe incorrect : le compte n\'a pas été supprimé.';
        } elseif ($manager->supprimer($id_utilisateur)) {
            Session::deconnecter();
            $compte_supprime = true;
        } else {
            $erreur_suppression = 'Le compte n\'a pas pu être supprimé. Réessaie plus tard.';
        }
    } else {
        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');

        if ($nom !== '' && $prenom !== '') {
            $manager->modifierNomPrenom($id_utilisateur, $nom, $prenom);

            Session::majIdentite($nom, $prenom);
            $message = 'Profil mis à jour.';
            $utilisateur = $manager->trouverParId($id_utilisateur);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon profil</title>
</head>
<body>

<?php $racine = '../'; require __DIR__ . '/../includes/header.php'; ?>

<?php if ($compte_supprime): ?>

<h1>Compte supprimé</h1>
<p>Ton compte et toutes tes données (profil, CV, candidatures, inscriptions, messages) ont été supprimés.</p>
<p><a href="../accueil/accueil.php">Retour à l'accueil</a></p>

<?php else: ?>

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

<?php if (!$utilisateur instanceof NewGestionnaire): ?>
    <h2>Supprimer mon compte</h2>
    <p>
        Ton compte et toutes tes données seront supprimés définitivement : profil, CV, candidatures,
        inscriptions aux événements et messages du forum.
        Voir la <a href="politique_confidentialite.php">politique de confidentialité</a>.
    </p>

    <?php if ($erreur_suppression): ?>
        <p style="color:red;"><?= htmlspecialchars($erreur_suppression) ?></p>
    <?php endif; ?>

    <form method="post" onsubmit="return confirm('Supprimer définitivement ton compte et toutes tes données ?');">
        <?= Session::champCsrf() ?>
        <input type="hidden" name="action" value="supprimer">
        <label>Confirme avec ton mot de passe</label>
        <input type="password" name="mot_de_passe" required><br>
        <button type="submit" style="color:red;">Supprimer mon compte</button>
    </form>
<?php endif; ?>

<?php endif; ?>

</body>
</html>