<?php
require __DIR__ . '/../includes/config.php';

Session::exigerConnexion('../');

$manager = new UtilisateurRepository();
$id_utilisateur = Session::getIdUtilisateur();
$utilisateur = $manager->trouverParId($id_utilisateur);

$message = '';
$erreur_suppression = '';
$compte_supprime = false;
$erreurs_mdp = [];
$message_mdp = '';
$erreur_cv = '';
$message_cv = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'modifier';

    if ($action === 'cv' && $utilisateur instanceof NewEtudiant) {
        // Mêmes vérifications qu'à l'inscription : PDF uniquement, 2 Mo maximum
        $fichier = $_FILES['cv'] ?? null;

        if ($fichier === null || empty($fichier['name'])) {
            $erreur_cv = 'Choisis un fichier PDF.';
        } elseif ($fichier['error'] !== UPLOAD_ERR_OK) {
            $erreur_cv = "Erreur lors de l'envoi du CV.";
        } elseif (strtolower(pathinfo($fichier['name'], PATHINFO_EXTENSION)) !== 'pdf'
            || mime_content_type($fichier['tmp_name']) !== 'application/pdf') {
            $erreur_cv = 'Le CV doit être un fichier PDF.';
        } elseif ($fichier['size'] > 2 * 1024 * 1024) {
            $erreur_cv = 'Le CV ne doit pas dépasser 2 Mo.';
        } else {
            $dossier_cv = __DIR__ . '/../uploads/cv/';
            if (!is_dir($dossier_cv)) {
                mkdir($dossier_cv, 0755, true);
            }
            // Nom aléatoire : on ne peut pas deviner l'adresse du CV d'un autre étudiant
            $nom_fichier = bin2hex(random_bytes(16)) . '.pdf';

            if (move_uploaded_file($fichier['tmp_name'], $dossier_cv . $nom_fichier)) {
                $manager->changerCv($id_utilisateur, 'uploads/cv/' . $nom_fichier);
                $message_cv = 'CV mis à jour.';
                $utilisateur = $manager->trouverParId($id_utilisateur);
            } else {
                $erreur_cv = "Le CV n'a pas pu être enregistré. Réessaie.";
            }
        }
    } elseif ($action === 'retirer_cv' && $utilisateur instanceof NewEtudiant) {
        $manager->changerCv($id_utilisateur, null);
        $message_cv = 'CV retiré.';
        $utilisateur = $manager->trouverParId($id_utilisateur);
    } elseif ($action === 'mot_de_passe') {
        // On redemande l'ancien mot de passe : si quelqu'un utilise la session ouverte, il ne peut pas le changer
        $ancien       = $_POST['ancien_mot_de_passe'] ?? '';
        $nouveau      = $_POST['nouveau_mot_de_passe'] ?? '';
        $confirmation = $_POST['confirmation'] ?? '';

        if (!$utilisateur->verifierMotDePasse($ancien)) {
            $erreurs_mdp[] = 'Mot de passe actuel incorrect.';
        } elseif ($nouveau !== $confirmation) {
            $erreurs_mdp[] = 'Les deux nouveaux mots de passe sont différents.';
        } elseif ($nouveau === $ancien) {
            $erreurs_mdp[] = 'Le nouveau mot de passe doit être différent de l\'ancien.';
        } else {
            // Mêmes règles qu'à l'inscription (longueur, majuscule, minuscule, chiffre, caractère spécial)
            $erreurs_mdp = NewUtilisateur::controlerRobustesse($nouveau);
        }

        if (empty($erreurs_mdp)) {
            $manager->changerMotDePasse($id_utilisateur, $nouveau);
            $message_mdp = 'Mot de passe modifié.';
            $utilisateur = $manager->trouverParId($id_utilisateur);
        }
    } elseif ($action === 'supprimer') {
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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mon profil</title>
    <link rel="stylesheet" href="../assets/css/css.css">
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

    <?php if ($utilisateur instanceof NewEtudiant): ?>
        <h2>Mon CV</h2>

        <?php if ($message_cv): ?>
            <p class="alerte-succes"><?= htmlspecialchars($message_cv) ?></p>
        <?php endif; ?>
        <?php if ($erreur_cv): ?>
            <p class="alerte-erreur"><?= htmlspecialchars($erreur_cv) ?></p>
        <?php endif; ?>

        <?php if ($utilisateur->getCv()): ?>
            <p>
                <a href="../<?= htmlspecialchars($utilisateur->getCv()) ?>" target="_blank" rel="noopener">Voir mon CV actuel</a>
            </p>
        <?php else: ?>
            <p>Tu n'as pas encore déposé de CV.</p>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <?= Session::champCsrf() ?>
            <input type="hidden" name="action" value="cv">
            <label><?= $utilisateur->getCv() ? 'Remplacer mon CV' : 'Déposer mon CV' ?> (PDF, 2 Mo maximum)</label>
            <input type="file" name="cv" accept="application/pdf" required><br>
            <button type="submit">Envoyer</button>
        </form>

        <?php if ($utilisateur->getCv()): ?>
            <form method="post" onsubmit="return confirm('Retirer ton CV ?');">
                <?= Session::champCsrf() ?>
                <input type="hidden" name="action" value="retirer_cv">
                <button type="submit" class="btn-refuser">Retirer mon CV</button>
            </form>
        <?php endif; ?>
    <?php endif; ?>

    <h2>Changer mon mot de passe</h2>

    <?php if ($message_mdp): ?>
        <p class="alerte-succes"><?= htmlspecialchars($message_mdp) ?></p>
    <?php endif; ?>

    <?php if (!empty($erreurs_mdp)): ?>
        <ul class="erreurs">
            <?php foreach ($erreurs_mdp as $erreur): ?>
                <li><?= htmlspecialchars($erreur) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="post">
        <?= Session::champCsrf() ?>
        <input type="hidden" name="action" value="mot_de_passe">

        <label>Mot de passe actuel</label>
        <input type="password" name="ancien_mot_de_passe" required autocomplete="current-password"><br>

        <label>Nouveau mot de passe</label>
        <input type="password" name="nouveau_mot_de_passe" required autocomplete="new-password"><br>

        <label>Confirme le nouveau mot de passe</label>
        <input type="password" name="confirmation" required autocomplete="new-password"><br>

        <button type="submit">Changer mon mot de passe</button>
    </form>

    <h2>Mes données personnelles</h2>
    <p>
        Tu peux télécharger une copie de toutes tes données (compte, profil, candidatures,
        inscriptions aux événements, offres et messages du forum) dans un fichier lisible (JSON).
    </p>
    <p><a href="mes_donnees.php" class="btn">Télécharger mes données</a></p>

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
            <button type="submit" class="btn-refuser">Supprimer mon compte</button>
        </form>
    <?php endif; ?>

    <?php require __DIR__ . '/' . $racine . 'includes/footer.php'; ?>

<?php endif; ?>

</body>
</html>