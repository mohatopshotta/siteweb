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

<?php
$pris        = $evenement->getPlacesPrises();
$total       = max(1, $evenement->getNombrePlaces());
$pct         = min(100, (int) round($pris / $total * 100));
$restantes   = $evenement->getPlacesRestantes();
$classeJauge = $evenement->estComplet() ? 'jauge-pleine' : ($restantes <= 5 ? 'jauge-peu' : '');
$estOrga     = $inscription !== null && $inscription->estOrganisateur();
?>
<a href="liste.php">&larr; Tous les événements</a>

<header class="fiche-entete<?= $evenement->estAnnule() ? ' fiche-annulee' : '' ?>">
    <p class="carte-type"><?= htmlspecialchars($evenement->getTypeEvenement()) ?></p>
    <h1><?= htmlspecialchars($evenement->getTitre()) ?></h1>
    <p class="fiche-statut">
        <?php if ($evenement->estAnnule()): ?>
            <span class="badge badge-annule">Annulé</span>
        <?php elseif ($evenement->estPasse()): ?>
            <span class="badge badge-passe">Terminé</span>
        <?php elseif ($evenement->estComplet()): ?>
            <span class="badge badge-complet">Complet</span>
        <?php elseif ($restantes <= 5): ?>
            <span class="badge badge-peu">Plus que <?= $restantes ?> place(s)</span>
        <?php else: ?>
            <span class="badge badge-dispo"><?= $restantes ?> places restantes</span>
        <?php endif; ?>

        <?php if ($estOrga): ?>
            <span class="badge badge-orga">Tu organises</span>
        <?php elseif ($inscription !== null): ?>
            <span class="badge badge-orga">&#10003; Tu es inscrit(e)</span>
        <?php endif; ?>
    </p>
</header>

<?php if ($evenement->estAnnule()): ?>
    <p class="avis-annule"><strong>Cet événement est annulé.</strong> Les inscriptions sont fermées.</p>
<?php endif; ?>

<?php if ($message): ?>
    <p class="message-succes"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<?php if ($erreur): ?>
    <ul class="erreurs"><li><?= htmlspecialchars($erreur) ?></li></ul>
<?php endif; ?>

<section class="fiche-infos">
    <div class="fiche-info">
        <span class="fiche-icone" aria-hidden="true">&#128197;</span>
        <div>
            <p class="fiche-label">Date</p>
            <p class="fiche-valeur"><?= htmlspecialchars($evenement->getDateAffichage()) ?></p>
            <?php if ($evenement->getDateEvenement() !== null && !$evenement->estPasse() && !$evenement->estAnnule()): ?>
                <a href="calendrier.php?id=<?= $evenement->getIdEvenement() ?>">+ Ajouter à mon calendrier</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="fiche-info">
        <span class="fiche-icone" aria-hidden="true">&#128205;</span>
        <div>
            <p class="fiche-label">Lieu</p>
            <p class="fiche-valeur"><?= htmlspecialchars($evenement->getLieu()) ?></p>
        </div>
    </div>

    <div class="fiche-info">
        <span class="fiche-icone" aria-hidden="true">&#128101;</span>
        <div>
            <p class="fiche-label">Places</p>
            <p class="fiche-valeur"><?= $pris ?> / <?= $evenement->getNombrePlaces() ?> inscrits</p>
            <div class="jauge <?= $classeJauge ?>" role="img"
                 aria-label="<?= $pris ?> inscrits sur <?= $evenement->getNombrePlaces() ?> places">
                <span style="width: <?= $pct ?>%"></span>
            </div>
            <p class="fiche-detail">
                <?= $evenement->estComplet() ? 'Complet' : $restantes . ' place(s) restante(s)' ?>
            </p>
        </div>
    </div>
</section>

<section class="fiche-bloc">
    <h2>À propos</h2>
    <p><?= nl2br(htmlspecialchars($evenement->getDescription())) ?></p>
</section>

<?php if ($evenement->getElementsRequis() !== null): ?>
    <section class="fiche-bloc fiche-requis">
        <h2>&#9888; Éléments requis</h2>
        <p><?= nl2br(htmlspecialchars($evenement->getElementsRequis())) ?></p>
    </section>
<?php endif; ?>

<?php if ($estOrga): ?>
    <section class="fiche-action">
        <p>Tu es organisateur de cet événement.</p>
        <a class="bouton" href="gerer.php?id=<?= $evenement->getIdEvenement() ?>">Gérer l'événement et les inscrits</a>
    </section>

    <?php $inscrits = $manager->listerInscrits($id_evenement); ?>
    <section class="fiche-bloc">
        <h2>Participants (<?= $pris ?>)</h2>
        <?php if ($pris === 0): ?>
            <p>Personne ne s'est encore inscrit.</p>
        <?php else: ?>
            <table>
                <thead>
                <tr><th>Nom</th><th>Email</th><th>Rôle</th></tr>
                </thead>
                <tbody>
                <?php foreach ($inscrits as $personne): ?>
                    <tr>
                        <td><?= htmlspecialchars($personne->getNomComplet()) ?></td>
                        <td><?= htmlspecialchars($personne->getEmail()) ?></td>
                        <td><?= $personne->estOrganisateur() ? 'Organisateur' : 'Participant' ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

<?php elseif ($inscription !== null): ?>
    <section class="fiche-action">
        <p>&#10003; Tu es inscrit(e) à cet événement.</p>
        <form method="post">
            <?= Session::champCsrf() ?>
            <button type="submit" name="desinscrire" onclick="return confirm('Se désinscrire de cet événement ?');">
                Se désinscrire
            </button>
        </form>
    </section>

<?php elseif ($peutSInscrire && !$evenement->estComplet() && !$evenement->estPasse() && !$evenement->estAnnule()): ?>
    <section class="fiche-action">
        <p><?= $restantes <= 5 ? 'Vite, il ne reste que ' . $restantes . ' place(s) !' : 'Il reste de la place, rejoins-nous !' ?></p>
        <form method="post">
            <?= Session::champCsrf() ?>
            <button type="submit" name="inscrire">S'inscrire</button>
        </form>
    </section>
<?php endif; ?>

<?php require __DIR__ . '/' . $racine . 'includes/footer.php'; ?>
</body>
</html>