<?php
require __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/page_erreur.php';

Session::exigerConnexion('../../');

$manager = new EvenementRepository();
$id_utilisateur = Session::getIdUtilisateur();
$id_evenement = (int)($_GET['id'] ?? 0);

$evenement = $manager->trouverParId($id_evenement);
if ($evenement === null) {
    pageErreur(404, 'Événement introuvable', "Cet événement n'existe pas ou a été supprimé.");
}

if (!$manager->estOrganisateur($id_utilisateur, $id_evenement)) {
    pageErreur(403, 'Accès réservé', 'Seuls les organisateurs de cet événement peuvent accéder à cette page.', 'detail.php?id=' . $id_evenement, "Voir l'événement");
}

// Envoie un mail à chaque participant (pas aux organisateurs). Renvoie le nombre de mails partis.
function prevenirParticipants(array $inscrits, string $sujet, string $corps): int
{
    $envoyes = 0;
    foreach ($inscrits as $personne) {
        if (!$personne->estOrganisateur() && Mail::envoyer($personne->getEmail(), $sujet, $corps)) {
            $envoyes++;
        }
    }
    return $envoyes;
}

$erreurs = [];
$inscrits = $manager->listerInscrits($id_evenement);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modifier'])) {
    $erreurs = NewEvenement::validerFormulaire($_POST);

    // Interdire de descendre sous le nombre d'inscrits actuel
    if (empty($erreurs) && (int)$_POST['nombre_places'] < $evenement->getPlacesPrises()) {
        $erreurs[] = "Il y a déjà " . $evenement->getPlacesPrises() . " participants inscrits : le nombre de places ne peut pas être inférieur.";
    }

    if (empty($erreurs)) {
        $nouveau = NewEvenement::depuisFormulaire($_POST, $id_evenement);

        // La date (début ou fin) ou le lieu a-t-il changé ?
        $dateChangee = $nouveau->getDateAffichage() !== $evenement->getDateAffichage();
        $lieuChange  = $nouveau->getLieu() !== $evenement->getLieu();

        $manager->modifier($nouveau);

        // Si oui, on prévient chaque participant par mail
        $prevenus = 0;
        $aPrevenir = $dateChangee || $lieuChange;
        if ($aPrevenir) {
            $corps = "Bonjour,\n\n"
                . "L'événement « " . $nouveau->getTitre() . " » a été modifié par son organisateur.\n\n";
            if ($dateChangee) {
                $corps .= "Date : " . $evenement->getDateAffichage() . "  ->  " . $nouveau->getDateAffichage() . "\n";
            }
            if ($lieuChange) {
                $corps .= "Lieu : " . $evenement->getLieu() . "  ->  " . $nouveau->getLieu() . "\n";
            }
            $corps .= "\nSi tu ne peux plus venir, tu peux te désinscrire depuis la page de l'événement.\n\n"
                . "L'équipe GDH - Hôpital Sud Paris";

            $prevenus = prevenirParticipants($inscrits, 'Modification : ' . $nouveau->getTitre(), $corps);
        }

        Session::flash($prevenus > 0
            ? 'Modifications enregistrées. ' . $prevenus . ' participant(s) prévenu(s) par mail.'
            : 'Modifications enregistrées.');
        Session::rediriger('gerer.php?id=' . $id_evenement);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_organisateur'])) {
    $erreurOrga = $manager->ajouterOrganisateur($_POST['email_organisateur'] ?? '', $id_evenement);
    if ($erreurOrga === '') {
        Session::flash('Organisateur ajouté.');
        Session::rediriger('gerer.php?id=' . $id_evenement);
    }
    $erreurs[] = $erreurOrga;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['annuler_evenement']) || isset($_POST['retablir_evenement']))) {
    $annule = isset($_POST['annuler_evenement']);
    $manager->definirAnnule($id_evenement, $annule);

    $corps = "Bonjour,\n\n"
        . ($annule
            ? "L'événement « " . $evenement->getTitre() . " » prévu le " . $evenement->getDateAffichage() . " est annulé.\n\nNous nous excusons pour la gêne occasionnée."
            : "Bonne nouvelle : l'événement « " . $evenement->getTitre() . " » est de nouveau maintenu (" . $evenement->getDateAffichage() . ", " . $evenement->getLieu() . ").")
        . "\n\nL'équipe GDH - Hôpital Sud Paris";
    $prevenus = prevenirParticipants($inscrits, ($annule ? 'Annulation : ' : 'Événement maintenu : ') . $evenement->getTitre(), $corps);

    Session::flash(($annule ? 'Événement annulé.' : 'Événement rétabli.')
        . ($prevenus > 0 ? ' ' . $prevenus . ' participant(s) prévenu(s) par mail.' : ''));
    Session::rediriger('gerer.php?id=' . $id_evenement);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['supprimer_evenement'])) {
    $manager->supprimer($id_evenement);
    Session::flash('Événement supprimé.');
    Session::rediriger('liste.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['retirer_id'])) {
    $manager->retirerParticipant((int)$_POST['retirer_id'], $id_evenement);
    Session::rediriger('gerer.php?id=' . $id_evenement);
}

$inscrits = $manager->listerInscrits($id_evenement);
$message  = Session::consommerFlash();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gérer — <?= htmlspecialchars($evenement->getTitre()) ?></title>
    <link rel="stylesheet" href="../../assets/css/css.css">
</head>
<body>

<?php $racine = '../../'; require __DIR__ . '/../../includes/header.php'; ?>

<a href="detail.php?id=<?= $id_evenement ?>">&larr; Retour à l'événement</a>

<h1>Gérer : <?= htmlspecialchars($evenement->getTitre()) ?></h1>

<?php if ($evenement->estAnnule()): ?>
    <p class="avis-annule"><strong>Cet événement est annulé.</strong> Les inscriptions sont fermées.</p>
<?php endif; ?>

<?php if ($message): ?>
    <p class="message-succes"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<?php if (!empty($erreurs)): ?>
    <ul class="erreurs">
        <?php foreach ($erreurs as $erreur): ?>
            <li><?= htmlspecialchars($erreur) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<h2>Modifier la fiche</h2>
<form method="post" action="gerer.php?id=<?= $id_evenement ?>">
    <?= Session::champCsrf() ?>

    <label for="type_evenement">Type d'événement</label>
    <input type="text" id="type_evenement" name="type_evenement"
           value="<?= htmlspecialchars($evenement->getTypeEvenement()) ?>" required>

    <label for="titre">Titre</label>
    <input type="text" id="titre" name="titre"
           value="<?= htmlspecialchars($evenement->getTitre()) ?>" required>

    <label for="description">Description</label>
    <textarea id="description" name="description" rows="5" required><?= htmlspecialchars($evenement->getDescription()) ?></textarea>

    <label for="lieu">Lieu</label>
    <input type="text" id="lieu" name="lieu"
           value="<?= htmlspecialchars($evenement->getLieu()) ?>" required>

    <label for="date_evenement">Date et heure</label>
    <input type="datetime-local" id="date_evenement" name="date_evenement"
           value="<?= htmlspecialchars($_POST['date_evenement'] ?? $evenement->getDateFormulaire()) ?>" required>

    <label for="date_fin">Heure de fin (facultatif)</label>
    <input type="datetime-local" id="date_fin" name="date_fin"
           value="<?= htmlspecialchars($_POST['date_fin'] ?? $evenement->getDateFinFormulaire()) ?>">

    <label for="elements_requis">Éléments requis (facultatif)</label>
    <textarea id="elements_requis" name="elements_requis" rows="3"><?= htmlspecialchars($evenement->getElementsRequis() ?? '') ?></textarea>

    <label for="nombre_places">Nombre de places</label>
    <input type="number" id="nombre_places" name="nombre_places" min="1"
           value="<?= $evenement->getNombrePlaces() ?>" required>

    <button type="submit" name="modifier">Enregistrer les modifications</button>
</form>

<h2>Inscrits (<?= count($inscrits) ?>)</h2>

<p><a href="export_inscrits.php?id=<?= $id_evenement ?>">&#11015; Exporter la liste (CSV)</a></p>

<table>
    <thead>
    <tr>
        <th>Nom</th>
        <th>Email</th>
        <th>Rôle</th>
        <th>Action</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($inscrits as $personne): ?>
        <tr>
            <td><?= htmlspecialchars($personne->getNomComplet()) ?></td>
            <td><?= htmlspecialchars($personne->getEmail()) ?></td>
            <td><?= $personne->estOrganisateur() ? 'Organisateur' : 'Participant' ?></td>
            <td>
                <?php if (!$personne->estOrganisateur()): ?>
                    <form method="post" action="gerer.php?id=<?= $id_evenement ?>" style="display:inline">
                        <?= Session::champCsrf() ?>
                        <input type="hidden" name="retirer_id" value="<?= $personne->getIdUtilisateur() ?>">
                        <button type="submit" onclick="return confirm('Retirer cette personne ?');">Retirer</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<h2>Ajouter un organisateur</h2>
<p>Un médecin ou un partenaire peut organiser cet événement avec toi.</p>
<form method="post" action="gerer.php?id=<?= $id_evenement ?>">
    <?= Session::champCsrf() ?>
    <label for="email_organisateur">Email de l'organisateur</label>
    <input type="email" id="email_organisateur" name="email_organisateur" required>
    <button type="submit" name="ajouter_organisateur">Ajouter</button>
</form>

<h2><?= $evenement->estAnnule() ? 'Rétablir l\'événement' : 'Annuler l\'événement' ?></h2>
<?php if ($evenement->estAnnule()): ?>
    <p>L'événement reste visible avec le badge « Annulé ». Tu peux le rétablir : les inscriptions rouvrent et les participants sont prévenus.</p>
    <form method="post" action="gerer.php?id=<?= $id_evenement ?>">
        <?= Session::champCsrf() ?>
        <button type="submit" name="retablir_evenement">Rétablir l'événement</button>
    </form>
<?php else: ?>
    <p>L'événement reste visible avec le badge « Annulé », les inscriptions sont fermées et les participants sont prévenus par mail. Contrairement à la suppression, rien n'est perdu.</p>
    <form method="post" action="gerer.php?id=<?= $id_evenement ?>">
        <?= Session::champCsrf() ?>
        <button type="submit" name="annuler_evenement"
                onclick="return confirm('Annuler cet événement ? Les participants seront prévenus par mail.');">
            Annuler l'événement
        </button>
    </form>
<?php endif; ?>

<h2>Zone dangereuse</h2>
<form method="post" action="gerer.php?id=<?= $id_evenement ?>">
    <?= Session::champCsrf() ?>
    <button type="submit" name="supprimer_evenement" class="bouton-danger"
            onclick="return confirm('Supprimer définitivement cet événement et toutes ses inscriptions ?');">
        Supprimer cet événement
    </button>
</form>

<?php require __DIR__ . '/' . $racine . 'includes/footer.php'; ?>
</body>
</html>