<?php
require_once __DIR__ . '/../../includes/config.php';

Session::exigerMembre('../../');

$offreRepository = new OffreRepository();

$type = $_GET['type'] ?? '';
$offres = $offreRepository->listerOuvertes($type);

$offres_entreprise = [];
if (Session::aLeRole(['partenaire'])) {
    $partenaire = (new UtilisateurRepository())->trouverParId(Session::getIdUtilisateur());
    $offres_entreprise = $offreRepository->listerParEntreprise($partenaire->getIdEntreprise());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nos offres d'emploi</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 30px 20px;
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            color: #243047;
        }

        .container {
            max-width: 1000px;
            margin: auto;
        }

        .entete {
            padding: 40px 25px;
            margin-bottom: 30px;
            text-align: center;
            color: white;
            background: linear-gradient(135deg, #2457c5, #6489ed);
            border-radius: 16px;
        }

        .entete h1 {
            margin-top: 0;
            font-size: 32px;
        }

        .entete p {
            font-size: 16px;
            line-height: 1.6;
        }

        .filtre {
            margin-bottom: 25px;
            padding: 20px;
            background: white;
            border-radius: 12px;
        }

        select {
            width: 100%;
            max-width: 350px;
            padding: 12px;
            border: 1px solid #ccd4e0;
            border-radius: 8px;
            font-size: 15px;
        }

        .offres {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 20px;
        }

        .offre {
            padding: 22px;
            background: white;
            border: 1px solid #e5eaf2;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(20, 40, 80, 0.05);
        }

        .offre h2 {
            margin-top: 0;
            font-size: 20px;
        }

        .offre h2 a {
            color: #2457c5;
            text-decoration: none;
        }

        .offre h2 a:hover {
            text-decoration: underline;
        }

        .entreprise {
            color: #58657a;
        }

        .badge {
            display: inline-block;
            margin: 8px 0;
            padding: 6px 10px;
            color: #2457c5;
            background: #eaf0ff;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .bouton {
            display: inline-block;
            margin-top: 10px;
            text-decoration: none;
            padding: 12px;
            color: white;
            background: #2457c5;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
        }

        .bouton:hover {
            background: #19439e;
        }

        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
        }

        .erreur {
            color: #a32121;
            background: #ffe8e8;
            border: 1px solid #f5bcbc;
        }

        .succes {
            color: #176534;
            background: #e5f8eb;
            border: 1px solid #a9e3b9;
        }

        .vide {
            padding: 30px;
            text-align: center;
            background: white;
            border-radius: 12px;
        }

        @media (max-width: 600px) {
            body {
                padding: 15px;
            }

            .entete h1 {
                font-size: 25px;
            }
        }
    </style>
</head>

<body>

<?php $racine = '../../'; require __DIR__ . '/../../includes/header.php'; ?>

<div class="container">

    <header class="entete">
        <h1>Découvrez toutes nos offres !</h1>
        <p>
            Vous êtes à la recherche d'un stage, d'une alternance
            ou d'un emploi ? Consultez toutes nos opportunités et
            choisissez l'offre qui correspond à votre projet professionnel.
        </p>
        <p>Votre prochaine opportunité commence ici !</p>
    </header>

    <?php if (Session::aLeRole(['partenaire'])): ?>
        <section class="filtre">
            <h2>Offres de mon entreprise</h2>
            <?php if (empty($offres_entreprise)): ?>
                <p>Aucune offre publiée par ton entreprise.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($offres_entreprise as $o): ?>
                        <li>
                            <a href="detail.php?id=<?= $o->getIdOffre() ?>"><?= htmlspecialchars($o->getTitre()) ?></a>
                            (<?= $o->estOuverte() ? 'ouverte' : 'clôturée' ?>)
                            — publiée par <?= htmlspecialchars($o->getNomAuteur()) ?>
                            <?php if ($o->getIdAuteur() === Session::getIdUtilisateur()): ?>
                                — <a href="gerer.php?id=<?= $o->getIdOffre() ?>">Gérer</a>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <a class="bouton" href="creer.php">+ Créer une offre</a>
        </section>
    <?php endif; ?>

    <section class="filtre">
        <h2>Rechercher une offre</h2>

        <form method="get">
            <select name="type" onchange="this.form.submit()">
                <option value="">Tous les types de contrats</option>

                <?php foreach (NewOffre::TYPES as $t): ?>
                    <option value="<?= $t ?>" <?= $type === $t ? 'selected' : '' ?>>
                        <?= strtoupper($t) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </section>

    <h2>Nos offres disponibles</h2>
    <p><?= count($offres) ?> offre(s) disponible(s)</p>

    <?php if (empty($offres)): ?>

        <div class="vide">
            <h3>Aucune offre disponible</h3>
            <p>De nouvelles opportunités seront bientôt proposées.</p>
        </div>

    <?php else: ?>

        <div class="offres">
            <?php foreach ($offres as $o): ?>
                <article class="offre">

                    <h2>
                        <a href="detail.php?id=<?= $o->getIdOffre() ?>">
                            <?= htmlspecialchars($o->getTitre()) ?>
                        </a>
                    </h2>

                    <p class="entreprise"><?= htmlspecialchars($o->getEntreprise()) ?></p>

                    <span class="badge"><?= strtoupper(htmlspecialchars($o->getTypeOffre())) ?></span>

                    <?php if ($o->getSalaire() !== null): ?>
                        <p>Salaire : <?= number_format($o->getSalaire(), 2, ',', ' ') ?> €</p>
                    <?php endif; ?>

                    <p>Publiée le <?= date('d/m/Y', strtotime($o->getDatePublication())) ?></p>

                    <a href="detail.php?id=<?= $o->getIdOffre() ?>">Voir les détails et postuler</a>

                </article>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

</div>

<?php require __DIR__ . '/' . $racine . 'includes/footer.php'; ?>
</body>
</html>
