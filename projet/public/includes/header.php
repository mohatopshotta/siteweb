<?php
require_once __DIR__ . '/../includes/config.php';
?>
<header>
    <nav>
        <a href="/accueil/accueil.php">Accueil</a>

        <?php if (!isset($_SESSION['id_utilisateur'])): ?>
            <!-- pas connecté : inscription et connexion -->
            <a href="/pages/inscription.php">Inscription</a>
            <a href="/pages/connexion.php">Connexion</a>

        <?php elseif ($_SESSION['role'] === 'gestionnaire'): ?>
            <!-- gestionnaire : accès à l'administration -->
            <a href="/pages/admin/admin.php">Administration</a>
            <a href="/pages/admin/valider_compte.php">Valider les comptes</a>
            <a href="/pages/deconnexion.php">Déconnexion</a>

        <?php else: ?>
            <!-- étudiant, médecin ou partenaire connecté -->
            <a href="/pages/offres/index.php">Offres</a>
            <a href="/pages/evenement/liste.php">Événements</a>
            <a href="/pages/forum.php">Forum</a>
            <a href="/pages/profil.php">Mon profil</a>
            <a href="/pages/deconnexion.php">Déconnexion</a>
        <?php endif; ?>
    </nav>
</header>