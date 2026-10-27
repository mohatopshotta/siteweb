<?php
// ============================================================
// pages/mentions_legales.php
// Mentions légales du site (obligatoires - loi LCEN)
// Les informations viennent de src/Core/InfosSite.php
// ============================================================

// config.php charge automatiquement les classes (dont InfosSite) et démarre la session
require __DIR__ . '/../includes/config.php';

$racine = '../';

// Fonction courte pour afficher une valeur en toute sécurité.
// function_exists évite l'erreur "Cannot redeclare" si un autre fichier la déclare déjà.
if (!function_exists('e')) {
    function e(string $texte): string
    {
        return htmlspecialchars($texte);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mentions légales - <?= e(InfosSite::NOM_SITE) ?></title>
    <link rel="stylesheet" href="<?= $racine ?>assets/css/css.css">
    <style>
        .page-legale { max-width: 900px; margin: 0 auto; padding: 20px; line-height: 1.6; }
        .page-legale h2 { margin-top: 30px; }
    </style>
</head>
<body>

<?php require __DIR__ . '/../includes/header.php'; ?>

<main class="page-legale">
    <h1>Mentions légales</h1>
    <p>Dernière mise à jour : <?= InfosSite::dateMiseAJour(__FILE__) ?></p>

    <h2>1. Éditeur du site</h2>
    <p>
        Le site <?= e(InfosSite::NOM_SITE) ?> est édité par le <strong><?= e(InfosSite::EDITEUR) ?></strong>
        pour le compte de l'<strong><?= e(InfosSite::ETABLISSEMENT) ?></strong>.<br>
        Adresse : <?= e(InfosSite::ADRESSE) ?><br>
        Téléphone : <?= e(InfosSite::TELEPHONE) ?><br>
        Email : <?= e(InfosSite::EMAIL) ?>
    </p>

    <h2>2. Directeur de la publication</h2>
    <p><?= e(InfosSite::DIRECTEUR_PUBLICATION) ?></p>

    <h2>3. Hébergement</h2>
    <p>
        Le site est hébergé sur un serveur virtuel par :<br>
        <?= e(InfosSite::HEBERGEUR_NOM) ?><br>
        <?= e(InfosSite::HEBERGEUR_ADRESSE) ?><br>
        <?= e(InfosSite::HEBERGEUR_TELEPHONE) ?>
    </p>

    <h2>4. Propriété intellectuelle</h2>
    <p>
        Les textes, logos et contenus du site appartiennent au <?= e(InfosSite::EDITEUR) ?>, sauf mention contraire.
        Toute reproduction sans autorisation est interdite.
        Les contenus publiés par les utilisateurs (offres, messages du forum, événements)
        restent sous la responsabilité de leurs auteurs.
    </p>

    <h2>5. Données personnelles</h2>
    <p>
        Le site collecte des données personnelles pour fonctionner (création de compte,
        candidatures, inscriptions aux événements, forum).
        Leur utilisation et vos droits sont expliqués dans la
        <a href="politique_confidentialite.php">politique de confidentialité</a>.
    </p>

    <h2>6. Cookies</h2>
    <p>
        Le site utilise seulement un cookie de session, nécessaire pour rester connecté.
        Il n'y a pas de cookie publicitaire ni de mesure d'audience.
        Plus de détails dans la <a href="politique_confidentialite.php#cookies">politique de confidentialité</a>.
    </p>
</main>

</body>
</html>
