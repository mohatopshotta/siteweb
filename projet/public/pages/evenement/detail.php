<?php
require __DIR__ . '/../../includes/config.php';

// Lecture ouverte à tout utilisateur connecté, gestionnaire compris (il ne peut pas agir)
Session::exigerConnexion('../../');

$manager = new EvenementRepository();
$id_utilisateur = Session::getIdUtilisateur();
$id_evenement = (int)($_GET['id'] ?? 0);

$evenement = $manager->trouverParId($id_evenement);
if ($evenement === null) {
    die("Événement introuvable.");
}


$erreur = '';

// Le gestionnaire consulte seulement : pas d'inscription
$peutSInscrire = Session::getRole() !== 'gestionnaire';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $peutSInscrire) {
    if (isset($_POST['inscrire'])) {
        if ($evenement->estPasse()) {
            $erreur = 'Cet événement est terminé.';
        } else {
            $erreur = $manager->inscrire($id_utilisateur, $evenement);
        }
        if ($erreur === '') {
            $mailEnvoye = false;
            $moi = (new UtilisateurRepository())->trouverParId($id_utilisateur);
            if ($moi !== null) {
                $mailEnvoye = Mail::envoyer(
                    $moi->getEmail(),
                    'Confirmation d\'inscription : ' . $evenement->getTitre(),
                    "Bonjour " . $moi->getPrenom() . ",\n\n"
                    . "Ton inscription à l'événement « " . $evenement->getTitre() . " » est bien enregistrée.\n"
                    . "Date : " . $evenement->getDateAffichage() . "\n"
                    . "Lieu : " . $evenement->getLieu() . "\n\n"
                    . "À bientôt,\nL'équipe GDH - Hôpital Sud Paris"
                );
            }
            Session::flash($mailEnvoye
                ? 'Inscription enregistrée. Un mail de confirmation t\'a été envoyé.'
                : 'Inscription enregistrée.');
        }
    } elseif (isset($_POST['desinscrire'])) {
        $manager->retirerParticipant($id_utilisateur, $id_evenement);
        Session::flash('Désinscription enregistrée.');
    }

    if ($erreur === '') {
        Session::rediriger('detail.php?id=' . $id_evenement);
    }
}

$inscription = $manager->trouverInscription($id_utilisateur, $id_evenement);
$message     = Session::consommerFlash();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($evenement->getTitre()) ?></title>
    <link rel="stylesheet" href="../../assets/css/css.css">
</head>
<body>

<?php $racine = '../../'; require __DIR__ . '/../../includes/header.php'; ?>

<a href="liste.php">&larr; Tous les événements</a>

<h1><?= htmlspecialchars($evenement->getTitre()) ?></h1>
<p><em><?= htmlspecialchars($evenement->getTypeEvenement()) ?></em></p>

<?php if ($message): ?>
    <p class="message-succes"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<p><strong>Date :</strong> <?= htmlspecialchars($evenement->getDateAffichage()) ?>
    <?php if ($evenement->estPasse()): ?><span class="badge badge-passe">Terminé</span><?php endif; ?>
</p>
<p><strong>Lieu :</strong> <?= htmlspecialchars($evenement->getLieu()) ?></p>
<p><?= nl2br(htmlspecialchars($evenement->getDescription())) ?></p>

<?php if ($evenement->getElementsRequis() !== null): ?>
    <h2>Éléments requis</h2>
    <p><?= nl2br(htmlspecialchars($evenement->getElementsRequis())) ?></p>
<?php endif; ?>

<p>
    <strong>Places :</strong>
    <?= $evenement->getPlacesPrises() ?> / <?= $evenement->getNombrePlaces() ?>
    <?php if ($evenement->estComplet()): ?>
        — Complet
    <?php else: ?>
        — <?= $evenement->getPlacesRestantes() ?> place(s) restante(s)
    <?php endif; ?>
</p>

<?php if ($erreur): ?>
    <p style="color:red;"><?= htmlspecialchars($erreur) ?></p>
<?php endif; ?>

<?php if ($inscription !== null && $inscription->estOrganisateur()): ?>
    <p>Tu es organisateur de cet événement.</p>
    <a href="gerer.php?id=<?= $evenement->getIdEvenement() ?>">Gérer l'événement et les inscrits</a>

<?php elseif ($inscription !== null): ?>
    <p>Tu es inscrit(e) à cet événement.</p>
    <form method="post">
        <?= Session::champCsrf() ?>
        <button type="submit" name="desinscrire" onclick="return confirm('Se désinscrire de cet événement ?');">
            Se désinscrire
        </button>
    </form>

<?php elseif ($peutSInscrire && !$evenement->estComplet() && !$evenement->estPasse()): ?>
    <form method="post">
        <?= Session::champCsrf() ?>
        <button type="submit" name="inscrire">S'inscrire</button>
    </form>
<?php endif; ?>

<?php require __DIR__ . '/' . $racine . 'includes/footer.php'; ?>
</body>
</html>
