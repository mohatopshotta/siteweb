<?php
require __DIR__ . '/../includes/config.php';

$erreur = '';
$envoye = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom     = trim($_POST['nom'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $sujet   = trim($_POST['sujet'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($nom === '' || $email === '' || $sujet === '' || $message === '') {
        $erreur = 'Tous les champs sont obligatoires.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = 'Email invalide.';
    } elseif (mb_strlen($nom) > 150 || mb_strlen($sujet) > 150) {
        $erreur = 'Le nom et le sujet ne doivent pas dépasser 150 caractères.';
    } else {
        try {
            (new ContactRepository())->creer(Session::getIdUtilisateur(), $nom, $email, $sujet, $message);
            foreach ((new UtilisateurRepository())->listerEmailsGestionnaires() as $emailGestionnaire) {
                Mail::envoyer(
                    $emailGestionnaire,
                    'Contact HSP : ' . str_replace(["\r", "\n"], ' ', $sujet),
                    "De : " . $nom . " <" . $email . ">\n\n" . $message
                );
            }
            $envoye = true;
        } catch (PDOException $e) {
            error_log('Contact : ' . $e->getMessage());
            $erreur = "Le message n'a pas pu être envoyé. Réessaie plus tard.";
        }
    }
}

$nomPrefill = $_POST['nom'] ?? (Session::estConnecte() ? trim(($_SESSION['prenom'] ?? '') . ' ' . ($_SESSION['nom'] ?? '')) : '');
$emailPrefill = $_POST['email'] ?? '';
if ($emailPrefill === '' && Session::estConnecte()) {
    $moi = (new UtilisateurRepository())->trouverParId(Session::getIdUtilisateur());
    $emailPrefill = $moi ? $moi->getEmail() : '';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Contact</title>
    <link rel="stylesheet" href="../assets/css/css.css">
</head>
<body>

<?php $racine = '../'; require __DIR__ . '/../includes/header.php'; ?>

<h1>Contact et support</h1>
<p>Une question, un problème technique ? Écris aux gestionnaires de la plateforme.</p>

<?php if ($envoye): ?>
    <p style="color:green;">Ton message a bien été envoyé aux gestionnaires.</p>
<?php else: ?>
    <?php if ($erreur): ?>
        <p style="color:red;"><?= htmlspecialchars($erreur) ?></p>
    <?php endif; ?>

    <form method="post">
        <?= Session::champCsrf() ?>

        <label for="nom">Nom</label>
        <input type="text" id="nom" name="nom" maxlength="150" value="<?= htmlspecialchars($nomPrefill) ?>" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($emailPrefill) ?>" required>

        <label for="sujet">Sujet</label>
        <input type="text" id="sujet" name="sujet" maxlength="150" value="<?= htmlspecialchars($_POST['sujet'] ?? '') ?>" required>

        <label for="message">Message</label>
        <textarea id="message" name="message" rows="6" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>

        <button type="submit">Envoyer</button>
    </form>
<?php endif; ?>

<?php require __DIR__ . '/' . $racine . 'includes/footer.php'; ?>
</body>
</html>
