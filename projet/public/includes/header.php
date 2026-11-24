<?php
$racine = $racine ?? '';

$connecte = Session::estConnecte();
$role     = Session::getRole();

// Marque le lien de la page affichée (aria-current) : repère visuel + lecteurs d'écran
$pageActuelle = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$courant = function (string $chemin) use ($pageActuelle): string {
    return str_ends_with($pageActuelle, '/' . $chemin) ? ' aria-current="page"' : '';
};
?>
<a class="lien-evitement" href="#contenu-principal">Aller au contenu</a>
<nav class="menu" aria-label="Menu principal">
    <a href="<?= $racine ?>accueil/accueil.php"<?= $courant('accueil/accueil.php') ?>>Accueil</a>

    <!-- Visibles par tout le monde. Un visiteur non connecté est renvoyé vers la connexion,
         un gestionnaire peut consulter (lecture seule). -->
    <a href="<?= $racine ?>pages/offres/index.php"<?= $courant('pages/offres/index.php') ?>>Offres</a>
    <a href="<?= $racine ?>pages/evenement/liste.php"<?= $courant('pages/evenement/liste.php') ?>>Événements</a>
    <a href="<?= $racine ?>pages/forum/liste.php"<?= $courant('pages/forum/liste.php') ?>>Forum</a>

    <?php if (in_array($role, ['etudiant', 'medecin', 'gestionnaire'], true)): ?>
        <a href="<?= $racine ?>pages/annuaire/medecins.php"<?= $courant('pages/annuaire/medecins.php') ?>>Annuaire médecins</a>
    <?php endif; ?>
    <?php if (in_array($role, ['medecin', 'gestionnaire'], true)): ?>
        <a href="<?= $racine ?>pages/annuaire/etudiants.php"<?= $courant('pages/annuaire/etudiants.php') ?>>Annuaire étudiants</a>
    <?php endif; ?>

    <a href="<?= $racine ?>pages/contact.php"<?= $courant('pages/contact.php') ?>>Contact</a>

    <?php if ($connecte && $role !== 'gestionnaire'): ?>
        <a href="<?= $racine ?>pages/profil.php"<?= $courant('pages/profil.php') ?>>Mon profil</a>
    <?php endif; ?>

    <?php if ($role === 'gestionnaire'): ?>
        <a href="<?= $racine ?>pages/admin/admin.php"<?= $courant('pages/admin/admin.php') ?>>Administration</a>
    <?php endif; ?>

    <?php if ($connecte): ?>
        <a href="<?= $racine ?>pages/deconnexion.php">Déconnexion</a>
    <?php else: ?>
        <a href="<?= $racine ?>pages/connexion.php"<?= $courant('pages/connexion.php') ?>>Connexion</a>
        <a href="<?= $racine ?>pages/inscription.php"<?= $courant('pages/inscription.php') ?>>Inscription</a>
    <?php endif; ?>
</nav>
<span id="contenu-principal" class="ancre-contenu" tabindex="-1"></span>