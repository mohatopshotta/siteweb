<?php
require_once __DIR__ . '/../../includes/config.php';

Session::exigerRole(['gestionnaire'], '../../');

$utilisateurs = new UtilisateurRepository();
$specialiteRepo = new SpecialiteRepository();
$offreRepo = new OffreRepository();
$evenementRepo = new EvenementRepository();
$contactRepo = new ContactRepository();

$retour = '';
$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'creer_gestionnaire') {
        $erreur = $utilisateurs->creerGestionnaire(
            trim($_POST['nom'] ?? ''), trim($_POST['prenom'] ?? ''), trim($_POST['email'] ?? ''),
            $_POST['mot_de_passe'] ?? '', Session::getIdUtilisateur()
        );
        $retour = 'Gestionnaire créé.';
    } elseif ($action === 'ajouter_specialite') {
        $erreur = $specialiteRepo->ajouter($_POST['libelle'] ?? '');
        $retour = 'Spécialité ajoutée.';
    } elseif ($action === 'supprimer_specialite') {
        $erreur = $specialiteRepo->supprimer($id);
        $retour = 'Spécialité supprimée.';
    } elseif ($action === 'supprimer_offre') {
        $offreRepo->supprimer($id);
        $retour = 'Offre supprimée.';
    } elseif ($action === 'supprimer_evenement') {
        $evenementRepo->supprimer($id);
        $retour = 'Événement supprimé.';
    } elseif ($action === 'supprimer_message') {
        $contactRepo->supprimer($id);
        $retour = 'Message supprimé.';
    }

    if ($erreur === '' && $retour !== '') {
        Session::flash($retour);
        Session::rediriger('gestion.php');
    }
}

$message = Session::consommerFlash();
$specialites = $specialiteRepo->listerTous();
$offres = $offreRepo->listerToutes();
$evenements = $evenementRepo->rechercher(null, null, true);
$messages = $contactRepo->lister();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestion - HSP GDH</title>
    <link rel="stylesheet" href="../../assets/css/css.css">
</head>
<body>

<?php $racine = '../../'; require __DIR__ . '/../../includes/header.php'; ?>

<div class="admin-container">
    <h1>Gestion de la plateforme</h1>
    <p><a href="admin.php">&larr; Tableau de bord</a></p>

    <?php if ($message): ?>
        <div class="alerte alerte-succes"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($erreur): ?>
        <div class="alerte alerte-erreur"><?= htmlspecialchars($erreur) ?></div>
    <?php endif; ?>

    <section class="admin-section">
        <h2>Messages de contact (<?= count($messages) ?>)</h2>
        <?php if (empty($messages)): ?>
            <p class="alerte alerte-info">Aucun message.</p>
        <?php else: ?>
            <table class="admin-table">
                <thead><tr><th>Date</th><th>De</th><th>Sujet</th><th>Message</th><th>Action</th></tr></thead>
                <tbody>
                <?php foreach ($messages as $m): ?>
                    <tr>
                        <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($m['date_message']))) ?></td>
                        <td><?= htmlspecialchars($m['nom']) ?> (<?= htmlspecialchars($m['email']) ?>)</td>
                        <td><?= htmlspecialchars($m['sujet']) ?></td>
                        <td><?= nl2br(htmlspecialchars($m['message'])) ?></td>
                        <td class="admin-actions">
                            <form method="post" class="form-inline" onsubmit="return confirm('Supprimer ce message ?');">
                                <?= Session::champCsrf() ?>
                                <input type="hidden" name="action" value="supprimer_message">
                                <input type="hidden" name="id" value="<?= (int)$m['id_message'] ?>">
                                <button type="submit" class="btn btn-refuser">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <section class="admin-section">
        <h2>Spécialités médicales</h2>
        <form method="post" class="form-inline">
            <?= Session::champCsrf() ?>
            <input type="hidden" name="action" value="ajouter_specialite">
            <input type="text" name="libelle" maxlength="100" placeholder="Nouvelle spécialité" required>
            <button type="submit" class="btn btn-valider">Ajouter</button>
        </form>
        <table class="admin-table">
            <thead><tr><th>Spécialité</th><th>Action</th></tr></thead>
            <tbody>
            <?php foreach ($specialites as $s): ?>
                <tr>
                    <td><?= htmlspecialchars($s->getLibelle()) ?></td>
                    <td class="admin-actions">
                        <form method="post" class="form-inline" onsubmit="return confirm('Supprimer cette spécialité ?');">
                            <?= Session::champCsrf() ?>
                            <input type="hidden" name="action" value="supprimer_specialite">
                            <input type="hidden" name="id" value="<?= $s->getIdSpecialite() ?>">
                            <button type="submit" class="btn btn-refuser">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <section class="admin-section">
        <h2>Offres (<?= count($offres) ?>)</h2>
        <?php if (empty($offres)): ?>
            <p class="alerte alerte-info">Aucune offre.</p>
        <?php else: ?>
            <table class="admin-table">
                <thead><tr><th>Titre</th><th>Entreprise</th><th>Type</th><th>État</th><th>Action</th></tr></thead>
                <tbody>
                <?php foreach ($offres as $o): ?>
                    <tr>
                        <td><a href="../offres/detail.php?id=<?= $o->getIdOffre() ?>"><?= htmlspecialchars($o->getTitre()) ?></a></td>
                        <td><?= htmlspecialchars($o->getEntreprise() ?? '') ?></td>
                        <td><?= htmlspecialchars($o->getTypeOffre()) ?></td>
                        <td><?= $o->estOuverte() ? 'ouverte' : 'clôturée' ?></td>
                        <td class="admin-actions">
                            <form method="post" class="form-inline" onsubmit="return confirm('Supprimer cette offre et ses candidatures ?');">
                                <?= Session::champCsrf() ?>
                                <input type="hidden" name="action" value="supprimer_offre">
                                <input type="hidden" name="id" value="<?= $o->getIdOffre() ?>">
                                <button type="submit" class="btn btn-refuser">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <section class="admin-section">
        <h2>Événements (<?= count($evenements) ?>)</h2>
        <?php if (empty($evenements)): ?>
            <p class="alerte alerte-info">Aucun événement.</p>
        <?php else: ?>
            <table class="admin-table">
                <thead><tr><th>Titre</th><th>Date</th><th>Lieu</th><th>Places</th><th>Action</th></tr></thead>
                <tbody>
                <?php foreach ($evenements as $e): ?>
                    <tr>
                        <td><a href="../evenement/detail.php?id=<?= $e->getIdEvenement() ?>"><?= htmlspecialchars($e->getTitre()) ?></a></td>
                        <td><?= htmlspecialchars($e->getDateAffichage()) ?></td>
                        <td><?= htmlspecialchars($e->getLieu()) ?></td>
                        <td><?= $e->getPlacesPrises() ?> / <?= $e->getNombrePlaces() ?></td>
                        <td class="admin-actions">
                            <form method="post" class="form-inline" onsubmit="return confirm('Supprimer cet événement et ses inscriptions ?');">
                                <?= Session::champCsrf() ?>
                                <input type="hidden" name="action" value="supprimer_evenement">
                                <input type="hidden" name="id" value="<?= $e->getIdEvenement() ?>">
                                <button type="submit" class="btn btn-refuser">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <section class="admin-section">
        <h2>Créer un gestionnaire</h2>
        <form method="post">
            <?= Session::champCsrf() ?>
            <input type="hidden" name="action" value="creer_gestionnaire">
            <label for="g_nom">Nom</label>
            <input type="text" id="g_nom" name="nom" required>
            <label for="g_prenom">Prénom</label>
            <input type="text" id="g_prenom" name="prenom" required>
            <label for="g_email">Email</label>
            <input type="email" id="g_email" name="email" required>
            <label for="g_mdp">Mot de passe (12 caractères min., majuscule, minuscule, chiffre, caractère spécial)</label>
            <input type="password" id="g_mdp" name="mot_de_passe" required>
            <button type="submit">Créer le gestionnaire</button>
        </form>
    </section>
</div>

<?php require __DIR__ . '/' . $racine . 'includes/footer.php'; ?>
</body>
</html>
