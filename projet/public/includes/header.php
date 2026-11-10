<?php
$racine = $racine ?? '';

$connecte = Session::estConnecte();
$role     = Session::getRole();
?>
<nav class="menu">
    <a href="<?= $racine ?>accueil/accueil.php">Accueil</a>

    <!-- Visibles par tout le monde. Un visiteur non connecté est renvoyé vers la connexion,
         un gestionnaire peut consulter (lecture seule). -->
    <a href="<?= $racine ?>pages/offres/index.php">Offres</a>
    <a href="<?= $racine ?>pages/evenement/liste.php">Événements</a>
    <a href="<?= $racine ?>pages/forum/liste.php">Forum</a>

    <?php if ($connecte && $role !== 'gestionnaire'): ?>
        <a href="<?= $racine ?>pages/profil.php">Mon profil</a>
    <?php endif; ?>

    <?php if ($role === 'gestionnaire'): ?>
        <a href="<?= $racine ?>pages/admin/admin.php">Administration</a>
    <?php endif; ?>

    <?php if ($connecte): ?>
        <a href="<?= $racine ?>pages/deconnexion.php">Déconnexion</a>
    <?php else: ?>
        <a href="<?= $racine ?>pages/connexion.php">Connexion</a>
        <a href="<?= $racine ?>pages/inscription.php">Inscription</a>
    <?php endif; ?>
</nav>
