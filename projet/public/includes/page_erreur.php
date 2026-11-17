<?php
/**
 * Affiche une vraie page d'erreur (menu, titre, message, lien de retour)
 * avec le bon code HTTP, puis arrête le script.
 * Utilisée par les pages du dossier pages/evenement/.
 */
function pageErreur(int $code, string $titre, string $message, string $lien = 'liste.php', string $texteLien = 'Retour aux événements'): void
{
    http_response_code($code);
    $racine = '../../';
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($titre) ?></title>
        <link rel="stylesheet" href="<?= $racine ?>assets/css/css.css">
    </head>
    <body>

    <?php require __DIR__ . '/header.php'; ?>

    <section class="vide">
        <h1><?= htmlspecialchars($titre) ?></h1>
        <p><?= htmlspecialchars($message) ?></p>
        <p><a class="bouton" href="<?= htmlspecialchars($lien) ?>">&larr; <?= htmlspecialchars($texteLien) ?></a></p>
    </section>

    <?php require __DIR__ . '/footer.php'; ?>
    </body>
    </html>
    <?php
    exit;
}
