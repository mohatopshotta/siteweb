<?php
// Menu commun du site HSP.
// Avant de l'inclure, la page définit $racine = chemin relatif vers /public/
//   - page dans public/            -> $racine = '';
//   - page dans public/pages/      -> $racine = '../';
//   - page dans public/pages/xxx/  -> $racine = '../../';
$racine = $racine ?? '';

$connecte = isset($_SESSION['id_utilisateur']);
$profil   = $_SESSION['profil'] ?? '';
?>
<nav class="menu">
    <a href="<?= $racine ?>accueil/accueil.php">Accueil</a>

    <?php if ($connecte && $profil !== 'gestionnaire'): ?>
        <a href="<?= $racine ?>pages/offres/index.php">Offres</a>
        <a href="<?= $racine ?>pages/evenement/liste.php">Événements</a>
        <a href="<?= $racine ?>pages/forum/liste.php">Forum</a>
        <a href="<?= $racine ?>pages/profil.php">Mon profil</a>
    <?php endif; ?>

    <?php if ($profil === 'gestionnaire'): ?>
        <a href="<?= $racine ?>pages/admin/admin.php">Administration</a>
    <?php endif; ?>

    <?php if ($connecte): ?>
        <a href="<?= $racine ?>pages/deconnexion.php">Déconnexion</a>
    <?php else: ?>
        <a href="<?= $racine ?>pages/connexion.php">Connexion</a>
        <a href="<?= $racine ?>pages/inscription.php">Inscription</a>
    <?php endif; ?>
</nav>