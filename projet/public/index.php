<?php
require_once '../../includes/config.php';

$type = $_GET['type'] ?? '';
$erreur = '';
$succes = '';

$sql = "SELECT o.id_offre, o.titre, o.type_offre, o.salaire,
               o.date_publication, e.nom AS entreprise
        FROM offre o
        JOIN entreprise e ON e.id_entreprise = o.id_entreprise
        WHERE o.etat = 'ouvert'";

$params = [];

if (in_array($type, ['stage', 'alternance', 'cdd', 'cdi'], true)) {
    $sql .= " AND o.type_offre = ?";
    $params[] = $type;
}

$sql .= " ORDER BY o.date_publication DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$offres = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Traitement de la candidature
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_offre = filter_input(INPUT_POST, 'id_offre', FILTER_VALIDATE_INT);

    if (!$id_offre) {
        $erreur = "Veuillez sélectionner une offre avant d'envoyer votre candidature.";
    } else {
        // Vérifier que l'offre existe et est toujours ouverte
        $verification = $pdo->prepare(
            "SELECT id_offre FROM offre
             WHERE id_offre = ? AND etat = 'ouvert'"
        );
        $verification->execute([$id_offre]);

        if ($verification->fetch()) {
            /*
             * À compléter :
             * enregistrer la candidature dans la base de données
             * lorsque la table et les champs de candidature seront connus.
             */
            $succes = "Nous avons reçu votre candidature !";
        } else {
            $erreur = "Cette offre n'est plus disponible.";
        }
    }
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

        .choix {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 18px;
            cursor: pointer;
        }

        .choix input {
            width: 17px;
            height: 17px;
            accent-color: #2457c5;
        }

        .bouton {
            display: inline-block;
            width: 100%;
            margin-top: 20px;
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

    <?php if ($erreur): ?>
        <div class="message erreur" role="alert">
            <?= htmlspecialchars($erreur) ?>
        </div>
    <?php endif; ?>

    <?php if ($succes): ?>
        <div class="message succes" role="status">
            <?= htmlspecialchars($succes) ?>
        </div>
    <?php endif; ?>

    <section class="filtre">
        <h2>Rechercher une offre</h2>

        <form method="get">
            <select name="type" onchange="this.form.submit()">
                <option value="">Tous les types de contrats</option>

                <?php foreach (['stage', 'alternance', 'cdd', 'cdi'] as $t): ?>
                    <option
                        value="<?= htmlspecialchars($t) ?>"
                        <?= $type === $t ? 'selected' : '' ?>
                    >
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

        <form method="post">
            <div class="offres">

                <?php foreach ($offres as $o): ?>
                    <article class="offre">

                        <h2>
                            <a href="detail.php?id=<?= (int) $o['id_offre'] ?>">
                                <?= htmlspecialchars($o['titre']) ?>
                            </a>
                        </h2>

                        <p class="entreprise">
                            🏢 <?= htmlspecialchars($o['entreprise']) ?>
                        </p>

                        <span class="badge">
                            <?= strtoupper(htmlspecialchars($o['type_offre'])) ?>
                        </span>

                        <?php if ($o['salaire'] !== null && $o['salaire'] !== ''): ?>
                            <p>
                                💶 Salaire :
                                <?= htmlspecialchars((string) $o['salaire']) ?> €
                            </p>
                        <?php endif; ?>

                        <p>
                            📅 Publiée le
                            <?= date('d/m/Y', strtotime($o['date_publication'])) ?>
                        </p>

                        <label class="choix">
                            <input
                                type="radio"
                                name="id_offre"
                                value="<?= (int) $o['id_offre'] ?>"
                                required
                            >
                            <span>Je choisis cette offre</span>
                        </label>

                        <a href="detail.php?id=<?= (int) $o['id_offre'] ?>">
                            Voir les détails
                        </a>

                    </article>
                <?php endforeach; ?>

            </div>

            <button type="submit" class="bouton">
                Envoyer ma candidature
            </button>
        </form>

    <?php endif; ?>

</div>
</body>
</html>