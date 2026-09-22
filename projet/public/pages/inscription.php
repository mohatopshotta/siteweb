<?php
require __DIR__ . '/../includes/config.php';

$erreurs = [];

$hopitaux = $pdo->query('SELECT id_hopital, nom FROM hopital ORDER BY nom')->fetchAll();
$specialites = $pdo->query('SELECT id_specialite, libelle FROM specialite ORDER BY libelle')->fetchAll();
$etablissements = $pdo->query('SELECT id_etablissement, nom FROM etablissement ORDER BY nom')->fetchAll();
$entreprises = $pdo->query('SELECT id_entreprise, nom FROM entreprise ORDER BY nom')->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
</head>
<body>

<h1>Inscription</h1>

<?php foreach ($erreurs as $erreur): ?>
    <p style="color:red;"><?= htmlspecialchars($erreur) ?></p>
<?php endforeach; ?>

<form method="post" enctype="multipart/form-data">

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

    <button type="submit">S'inscrire</button>
</form>

<p>Déjà inscrit ? <a href="connexion.php">Se connecter</a></p>

</body>
</html>
<div id="champs-etudiant" style="display:none;">
    <h3>Étudiant</h3>
    <label>Formation</label>
    <input type="text" name="formation"><br>

    <label>CV (facultatif)</label>
    <input type="file" name="cv"><br>

    <label>Établissement</label>
    <select name="id_etablissement" onchange="afficherNouvelEtablissement(this)">
        <option value="">-- choisir --</option>
        <?php foreach ($etablissements as $etab): ?>
            <option value="<?= $etab['id_etablissement'] ?>"><?= htmlspecialchars($etab['nom']) ?></option>
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
            <option value="<?= $spe['id_specialite'] ?>"><?= htmlspecialchars($spe['libelle']) ?></option>
        <?php endforeach; ?>
    </select><br>

    <label>Hôpital de rattachement</label>
    <select name="id_hopital">
        <option value="">-- choisir --</option>
        <?php foreach ($hopitaux as $hop): ?>
            <option value="<?= $hop['id_hopital'] ?>"><?= htmlspecialchars($hop['nom']) ?></option>
        <?php endforeach; ?>
    </select><br>

    <label>Établissement d'enseignement (facultatif)</label>
    <select name="id_etablissement_medecin" onchange="afficherNouvelEtablissement(this)">
        <option value="">-- aucun --</option>
        <?php foreach ($etablissements as $etab): ?>
            <option value="<?= $etab['id_etablissement'] ?>"><?= htmlspecialchars($etab['nom']) ?></option>
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
            <option value="<?= $ent['id_entreprise'] ?>"><?= htmlspecialchars($ent['nom']) ?></option>
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