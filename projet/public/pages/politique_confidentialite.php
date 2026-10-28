<?php
// ============================================================
// pages/politique_confidentialite.php
// Politique de confidentialité (RGPD)
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

// On récupère les listes une seule fois
$donnees        = InfosSite::getDonneesCollectees();
$finalites      = InfosSite::getFinalites();
$destinataires  = InfosSite::getDestinataires();
$durees         = InfosSite::getDureesConservation();
$droits         = InfosSite::getDroits();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Politique de confidentialité - <?= e(InfosSite::NOM_SITE) ?></title>
    <link rel="stylesheet" href="<?= $racine ?>assets/css/css.css">
    <style>
        .page-legale { max-width: 900px; margin: 0 auto; padding: 20px; line-height: 1.6; }
        .page-legale h2 { margin-top: 30px; }
        .page-legale table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        .page-legale th, .page-legale td { border: 1px solid #ccc; padding: 8px; text-align: left; vertical-align: top; }
        .page-legale th { background: #f4f4f4; }
    </style>
</head>
<body>

<?php require __DIR__ . '/../includes/header.php'; ?>

<main class="page-legale">
    <h1>Politique de confidentialité</h1>
    <p>Dernière mise à jour : <?= InfosSite::dateMiseAJour(__FILE__) ?></p>

    <p>
        Cette page explique quelles données personnelles le site <?= e(InfosSite::NOM_SITE) ?> collecte,
        pourquoi, combien de temps elles sont gardées et quels sont vos droits,
        conformément au Règlement général sur la protection des données (RGPD).
    </p>

    <h2>1. Responsable du traitement</h2>
    <p>
        Le responsable du traitement est le <?= e(InfosSite::EDITEUR) ?>, pour l'<?= e(InfosSite::ETABLISSEMENT) ?>.<br>
        Contact pour toute question sur vos données : <?= e(InfosSite::EMAIL_DPO) ?>
    </p>

    <h2>2. Données collectées</h2>
    <table>
        <tr>
            <th>Qui</th>
            <th>Données</th>
        </tr>
        <?php foreach ($donnees as $qui => $liste): ?>
            <tr>
                <td><?= e($qui) ?></td>
                <td><?= e($liste) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <p>Nous ne collectons que les données nécessaires au fonctionnement du site.</p>

    <h2>3. Pourquoi ces données sont utilisées</h2>
    <ul>
        <?php foreach ($finalites as $i => $finalite): ?>
            <li><?= e($finalite) ?><?= $i < count($finalites) - 1 ? ' ;' : '.' ?></li>
        <?php endforeach; ?>
    </ul>
    <p>
        Ces traitements sont nécessaires pour vous fournir le service que vous demandez
        en créant un compte (exécution du service). Vos données ne sont jamais vendues
        ni utilisées à des fins publicitaires.
    </p>

    <h2>4. Qui peut voir vos données</h2>
    <ul>
        <?php foreach ($destinataires as $qui => $quoi): ?>
            <li><strong><?= e($qui) ?></strong> : <?= e($quoi) ?> ;</li>
        <?php endforeach; ?>
    </ul>

    <h2>5. Durée de conservation</h2>
    <table>
        <tr>
            <th>Données</th>
            <th>Durée</th>
        </tr>
        <?php foreach ($durees as $quoi => $duree): ?>
            <tr>
                <td><?= e($quoi) ?></td>
                <td><?= e($duree) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <h2>6. Sécurité</h2>
    <p>
        Nous protégeons vos données : mots de passe hachés (ils ne peuvent pas être retrouvés en clair), accès aux pages
        d'administration réservé aux gestionnaires, comptes vérifiés avant activation
        et protections contre les attaques les plus courantes.
    </p>

    <h2>7. Vos droits</h2>
    <p>Vous pouvez à tout moment :</p>
    <ul>
        <?php foreach ($droits as $i => $droit): ?>
            <li><?= e($droit) ?><?= $i < count($droits) - 1 ? ' ;' : '.' ?></li>
        <?php endforeach; ?>
    </ul>
    <p>
        Pour cela, écrivez à <?= e(InfosSite::EMAIL_DPO) ?> depuis l'adresse email de votre compte.
        Une réponse vous sera donnée sous <?= e(InfosSite::DELAI_REPONSE) ?> maximum.
    </p>
    <p>
        Si vous pensez que vos droits ne sont pas respectés, vous pouvez faire une réclamation
        auprès de la CNIL : <a href="https://www.cnil.fr" target="_blank" rel="noopener">www.cnil.fr</a>.
    </p>

    <h2 id="cookies">8. Cookies</h2>
    <p>
        Le site utilise un seul cookie : le cookie de session (<code><?= e(session_name()) ?></code>).
        Il sert uniquement à vous garder connecté pendant votre visite et il est supprimé
        quand vous fermez votre navigateur. Comme il est indispensable au fonctionnement du site,
        il ne nécessite pas votre consentement.
        Le site n'utilise ni cookie publicitaire ni outil de mesure d'audience.
    </p>
</main>

<?php require __DIR__ . '/' . $racine . 'includes/footer.php'; ?>
</body>
</html>
