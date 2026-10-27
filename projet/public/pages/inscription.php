<?php
require __DIR__ . '/../includes/config.php';

$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $role = $_POST['role'] ?? '';

    if ($nom === '' || $prenom === '' || $email === '' || $mot_de_passe === '') {
        $erreurs[] = 'Tous les champs sont obligatoires.';
    }
    if ($mot_de_passe !== '') {
        $erreurs = array_merge($erreurs, Utilisateur::controlerRobustesse($mot_de_passe));
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = 'Email invalide.';
    }
    if (!in_array($role, ['etudiant', 'medecin', 'partenaire'], true)) {
        $erreurs[] = 'Rôle invalide.';
    }

    $utilisateurManager = new UtilisateurManager();

    if (empty($erreurs) && $utilisateurManager->emailExiste($email)) {
        $erreurs[] = 'Cet email est déjà utilisé.';
    }

    $nouvel_etablissement = null;
    $nouvelle_entreprise = null;
    $infos_etablissement = [
        'nom'      => trim($_POST['nouvel_etablissement_nom'] ?? ''),
        'adresse'  => trim($_POST['nouvel_etablissement_adresse'] ?? ''),
        'site_web' => trim($_POST['nouvel_etablissement_site'] ?? ''),
    ];

    $utilisateur = null;

    if (empty($erreurs) && $role === 'etudiant') {
        $formation = trim($_POST['formation'] ?? '');
        $id_etablissement = $_POST['id_etablissement'] ?? '';
        if ($formation === '') {
            $erreurs[] = 'La formation est obligatoire.';
        }
        if ($id_etablissement === 'nouveau') {
            if (in_array('', $infos_etablissement, true)) {
                $erreurs[] = 'Les informations du nouvel établissement sont incomplètes.';
            }
            $nouvel_etablissement = new Etablissement($infos_etablissement);
        } elseif (!ctype_digit($id_etablissement)) {
            $erreurs[] = 'Choisis un établissement.';
        }

        if (!empty($_FILES['cv']['name'])) {
            $extension = strtolower(pathinfo($_FILES['cv']['name'], PATHINFO_EXTENSION));
            if ($_FILES['cv']['error'] !== UPLOAD_ERR_OK) {
                $erreurs[] = "Erreur lors de l'envoi du CV.";
            } elseif ($extension !== 'pdf' || mime_content_type($_FILES['cv']['tmp_name']) !== 'application/pdf') {
                $erreurs[] = 'Le CV doit être un fichier PDF.';
            } elseif ($_FILES['cv']['size'] > 2 * 1024 * 1024) {
                $erreurs[] = 'Le CV ne doit pas dépasser 2 Mo.';
            }
        }

        $utilisateur = new Etudiant([
            'formation'        => $formation,
            'id_etablissement' => ctype_digit($id_etablissement) ? (int)$id_etablissement : null,
        ]);
    }

    if (empty($erreurs) && $role === 'medecin') {
        $id_specialite = $_POST['id_specialite'] ?? '';
        $id_hopital = $_POST['id_hopital'] ?? '';
        $id_etablissement_medecin = $_POST['id_etablissement_medecin'] ?? '';
        if (!ctype_digit($id_specialite) || !ctype_digit($id_hopital)) {
            $erreurs[] = 'Spécialité et hôpital sont obligatoires.';
        }
        if ($id_etablissement_medecin === 'nouveau') {
            if (in_array('', $infos_etablissement, true)) {
                $erreurs[] = 'Les informations du nouvel établissement sont incomplètes.';
            }
            $nouvel_etablissement = new Etablissement($infos_etablissement);
        }

        $utilisateur = new Medecin([
            'id_specialite'    => (int)$id_specialite,
            'id_hopital'       => (int)$id_hopital,
            'id_etablissement' => ctype_digit($id_etablissement_medecin) ? (int)$id_etablissement_medecin : null,
        ]);
    }

    if (empty($erreurs) && $role === 'partenaire') {
        $poste = trim($_POST['poste'] ?? '');
        $id_entreprise = $_POST['id_entreprise'] ?? '';
        if ($poste === '') {
            $erreurs[] = 'Le poste est obligatoire.';
        }
        if ($id_entreprise === 'nouveau') {
            $infos_entreprise = [
                'nom'      => trim($_POST['nouvelle_entreprise_nom'] ?? ''),
                'adresse'  => trim($_POST['nouvelle_entreprise_adresse'] ?? ''),
                'site_web' => trim($_POST['nouvelle_entreprise_site'] ?? ''),
            ];
            if (in_array('', $infos_entreprise, true)) {
                $erreurs[] = "Les informations de la nouvelle entreprise sont incomplètes.";
            }
            $nouvelle_entreprise = new Entreprise($infos_entreprise);
        } elseif (!ctype_digit($id_entreprise)) {
            $erreurs[] = 'Choisis une entreprise.';
        }

        $utilisateur = new Partenaire([
            'poste'         => $poste,
            'id_entreprise' => ctype_digit($id_entreprise) ? (int)$id_entreprise : null,
        ]);
    }

    if (empty($erreurs) && $utilisateur !== null) {
        $utilisateur->hydrater(['nom' => $nom, 'prenom' => $prenom, 'email' => $email]);

        $fichier_cv = null;
        if ($utilisateur instanceof Etudiant && !empty($_FILES['cv']['name'])) {
            $dossier_cv = __DIR__ . '/../uploads/cv/';
            if (!is_dir($dossier_cv)) {
                mkdir($dossier_cv, 0755, true);
            }

            $nom_fichier = bin2hex(random_bytes(16)) . '.pdf';
            $fichier_cv = $dossier_cv . $nom_fichier;
            if (move_uploaded_file($_FILES['cv']['tmp_name'], $fichier_cv)) {
                $utilisateur->setCv('uploads/cv/' . $nom_fichier);
            } else {
                $fichier_cv = null;
                $erreurs[] = "Le CV n'a pas pu être enregistré. Réessaie.";
            }
        }

        if (empty($erreurs)) {
            try {
                $utilisateurManager->inscrire($utilisateur, $mot_de_passe, $nouvel_etablissement, $nouvelle_entreprise);

                Session::rediriger('connexion.php?inscription=ok');
            } catch (Exception $e) {
                if ($fichier_cv !== null && is_file($fichier_cv)) {
                    unlink($fichier_cv);
                }
                error_log('Inscription : ' . $e->getMessage());
                $erreurs[] = "L'inscription n'a pas pu être enregistrée. Réessaie plus tard.";
            }
        }
    }
}

$hopitaux = (new HopitalManager())->listerTous();
$specialites = (new SpecialiteManager())->listerTous();
$etablissements = (new EtablissementManager())->listerTous();
$entreprises = (new EntrepriseManager())->listerTous();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
</head>
<body>

<?php $racine = '../'; require __DIR__ . '/../includes/header.php'; ?>

<h1>Inscription</h1>

<?php foreach ($erreurs as $erreur): ?>
    <p style="color:red;"><?= htmlspecialchars($erreur) ?></p>
<?php endforeach; ?>

<form method="post" enctype="multipart/form-data">
    <?= Session::champCsrf() ?>

    <label>Nom</label>
    <input type="text" name="nom" value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" required><br>

    <label>Prénom</label>
    <input type="text" name="prenom" value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>" required><br>

    <label>Email</label>
    <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required><br>

    <label>Mot de passe</label>
    <input type="password" name="mot_de_passe" required><br>

    <label>Je suis</label>
    <select name="role" id="role" onchange="afficherChamps()" required>
        <option value="">-- choisir --</option>
        <option value="etudiant">Étudiant</option>
        <option value="medecin">Médecin</option>
        <option value="partenaire">Partenaire</option>
    </select><br>

    <div id="champs-etudiant" style="display:none;">
        <h3>Étudiant</h3>
        <label>Formation</label>
        <input type="text" name="formation"><br>

        <label>CV en PDF (facultatif, 2 Mo max)</label>
        <input type="file" name="cv" accept="application/pdf"><br>

        <label>Établissement</label>
        <select name="id_etablissement" onchange="afficherNouvelEtablissement(this)">
            <option value="">-- choisir --</option>
            <?php foreach ($etablissements as $etab): ?>
                <option value="<?= $etab->getIdEtablissement() ?>"><?= htmlspecialchars($etab->getNom()) ?></option>
            <?php endforeach; ?>
            <option value="nouveau">+ Nouvel établissement</option>
        </select><br>
    </div>

    <div id="champs-medecin" style="display:none;">
        <h3>Médecin</h3>
        <label>Spécialité</label>
        <select name="id_specialite">
            <option value="">-- choisir --</option>
            <?php foreach ($specialites as $spe): ?>
                <option value="<?= $spe->getIdSpecialite() ?>"><?= htmlspecialchars($spe->getLibelle()) ?></option>
            <?php endforeach; ?>
        </select><br>

        <label>Hôpital de rattachement</label>
        <select name="id_hopital">
            <option value="">-- choisir --</option>
            <?php foreach ($hopitaux as $hop): ?>
                <option value="<?= $hop->getIdHopital() ?>"><?= htmlspecialchars($hop->getNom()) ?></option>
            <?php endforeach; ?>
        </select><br>

        <label>Établissement d'enseignement (facultatif)</label>
        <select name="id_etablissement_medecin" onchange="afficherNouvelEtablissement(this)">
            <option value="">-- aucun --</option>
            <?php foreach ($etablissements as $etab): ?>
                <option value="<?= $etab->getIdEtablissement() ?>"><?= htmlspecialchars($etab->getNom()) ?></option>
            <?php endforeach; ?>
            <option value="nouveau">+ Nouvel établissement</option>
        </select><br>
    </div>

    <div id="champs-partenaire" style="display:none;">
        <h3>Partenaire</h3>
        <label>Poste occupé</label>
        <input type="text" name="poste"><br>

        <label>Entreprise</label>
        <select name="id_entreprise" onchange="afficherNouvelleEntreprise(this)">
            <option value="">-- choisir --</option>
            <?php foreach ($entreprises as $ent): ?>
                <option value="<?= $ent->getIdEntreprise() ?>"><?= htmlspecialchars($ent->getNom()) ?></option>
            <?php endforeach; ?>
            <option value="nouveau">+ Nouvelle entreprise</option>
        </select><br>
    </div>

    <div id="champs-nouvel-etablissement" style="display:none;">
        <h4>Nouvel établissement</h4>
        <input type="text" name="nouvel_etablissement_nom" placeholder="Nom"><br>
        <input type="text" name="nouvel_etablissement_adresse" placeholder="Adresse"><br>
        <input type="text" name="nouvel_etablissement_site" placeholder="Site web"><br>
    </div>

    <div id="champs-nouvelle-entreprise" style="display:none;">
        <h4>Nouvelle entreprise</h4>
        <input type="text" name="nouvelle_entreprise_nom" placeholder="Nom"><br>
        <input type="text" name="nouvelle_entreprise_adresse" placeholder="Adresse"><br>
        <input type="text" name="nouvelle_entreprise_site" placeholder="Site web"><br>
    </div>

    <button type="submit">S'inscrire</button>
</form>

<p>Déjà inscrit ? <a href="connexion.php">Se connecter</a></p>

<script>
    function afficherChamps() {
        const role = document.getElementById('role').value;
        document.getElementById('champs-etudiant').style.display = role === 'etudiant' ? 'block' : 'none';
        document.getElementById('champs-medecin').style.display = role === 'medecin' ? 'block' : 'none';
        document.getElementById('champs-partenaire').style.display = role === 'partenaire' ? 'block' : 'none';
        document.getElementById('champs-nouvel-etablissement').style.display = 'none';
        document.getElementById('champs-nouvelle-entreprise').style.display = 'none';
    }

    function afficherNouvelEtablissement(select) {
        document.getElementById('champs-nouvel-etablissement').style.display =
            select.value === 'nouveau' ? 'block' : 'none';
    }

    function afficherNouvelleEntreprise(select) {
        document.getElementById('champs-nouvelle-entreprise').style.display =
            select.value === 'nouveau' ? 'block' : 'none';
    }
</script>

</body>
</html>