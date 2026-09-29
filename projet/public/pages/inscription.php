<?php
require __DIR__ . '/../includes/config.php';

$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $role = $_POST['role'] ?? '';

    // champs obligatoires communs à tous les rôles
    if ($nom === '' || $prenom === '' || $email === '' || $mot_de_passe === '') {
        $erreurs[] = 'Tous les champs sont obligatoires.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = 'Email invalide.';
    }
    if (!in_array($role, ['etudiant', 'medecin', 'partenaire'], true)) {
        $erreurs[] = 'Rôle invalide.';
    }

    // on vérifie que l'email n'est pas déjà pris
    if (empty($erreurs)) {
        $req = $pdo->prepare('SELECT id_utilisateur FROM utilisateur WHERE email = ?');
        $req->execute([$email]);
        if ($req->fetch()) {
            $erreurs[] = 'Cet email est déjà utilisé.';
        }
    }

    // validation spécifique étudiant
    $formation = $id_etablissement = $cv_path = null;
    if (empty($erreurs) && $role === 'etudiant') {
        $formation = trim($_POST['formation'] ?? '');
        $id_etablissement = $_POST['id_etablissement'] ?? '';
        if ($formation === '') {
            $erreurs[] = 'La formation est obligatoire.';
        }
        if ($id_etablissement === 'nouveau') {
            // création d'un nouvel établissement
            $nom_etab = trim($_POST['nouvel_etablissement_nom'] ?? '');
            $adresse_etab = trim($_POST['nouvel_etablissement_adresse'] ?? '');
            $site_etab = trim($_POST['nouvel_etablissement_site'] ?? '');
            if ($nom_etab === '' || $adresse_etab === '' || $site_etab === '') {
                $erreurs[] = 'Les informations du nouvel établissement sont incomplètes.';
            }
        } elseif ($id_etablissement === '') {
            $erreurs[] = 'Choisis un établissement.';
        }
    }

    // validation spécifique médecin
    $id_specialite = $id_hopital = $id_etablissement_medecin = null;
    if (empty($erreurs) && $role === 'medecin') {
        $id_specialite = $_POST['id_specialite'] ?? '';
        $id_hopital = $_POST['id_hopital'] ?? '';
        if ($id_specialite === '' || $id_hopital === '') {
            $erreurs[] = 'Spécialité et hôpital sont obligatoires.';
        }
        $id_etablissement_medecin = $_POST['id_etablissement_medecin'] ?? '';
        if ($id_etablissement_medecin === 'nouveau') {
            $nom_etab = trim($_POST['nouvel_etablissement_nom'] ?? '');
            $adresse_etab = trim($_POST['nouvel_etablissement_adresse'] ?? '');
            $site_etab = trim($_POST['nouvel_etablissement_site'] ?? '');
            if ($nom_etab === '' || $adresse_etab === '' || $site_etab === '') {
                $erreurs[] = 'Les informations du nouvel établissement sont incomplètes.';
            }
        }
    }

    // validation spécifique partenaire
    $poste = $id_entreprise = null;
    if (empty($erreurs) && $role === 'partenaire') {
        $poste = trim($_POST['poste'] ?? '');
        $id_entreprise = $_POST['id_entreprise'] ?? '';
        if ($poste === '') {
            $erreurs[] = 'Le poste est obligatoire.';
        }
        if ($id_entreprise === 'nouveau') {
            $nom_ent = trim($_POST['nouvelle_entreprise_nom'] ?? '');
            $adresse_ent = trim($_POST['nouvelle_entreprise_adresse'] ?? '');
            $site_ent = trim($_POST['nouvelle_entreprise_site'] ?? '');
            if ($nom_ent === '' || $adresse_ent === '' || $site_ent === '') {
                $erreurs[] = "Les informations de la nouvelle entreprise sont incomplètes.";
            }
        } elseif ($id_entreprise === '') {
            $erreurs[] = 'Choisis une entreprise.';
        }
    }

    // tout est valide : on insère en base
    if (empty($erreurs)) {
        try {
            // transaction : si une insertion échoue, tout est annulé
            $pdo->beginTransaction();

            // création de l'utilisateur commun (pas encore validé par un gestionnaire)
            $req = $pdo->prepare('INSERT INTO utilisateur (nom, prenom, email, mot_de_passe) VALUES (?, ?, ?, ?)');
            $req->execute([$nom, $prenom, $email, password_hash($mot_de_passe, PASSWORD_DEFAULT)]);
            $id_utilisateur = $pdo->lastInsertId();

            if ($role === 'etudiant') {
                if ($id_etablissement === 'nouveau') {
                    // on vérifie si un établissement avec ce site web existe déjà (anti-doublon)
                    $req = $pdo->prepare('SELECT id_etablissement FROM etablissement WHERE site_web = ?');
                    $req->execute([$site_etab]);
                    $existant = $req->fetch();
                    if ($existant) {
                        $id_etablissement = $existant['id_etablissement'];
                    } else {
                        $req = $pdo->prepare('INSERT INTO etablissement (nom, adresse, site_web) VALUES (?, ?, ?)');
                        $req->execute([$nom_etab, $adresse_etab, $site_etab]);
                        $id_etablissement = $pdo->lastInsertId();
                    }
                }

                // upload du CV si fourni
                if (!empty($_FILES['cv']['name'])) {
                    $dossier_cv = __DIR__ . '/../uploads/cv/';
                    if (!is_dir($dossier_cv)) {
                        mkdir($dossier_cv, 0755, true);
                    }
                    $nom_fichier = uniqid() . '_' . basename($_FILES['cv']['name']);
                    move_uploaded_file($_FILES['cv']['tmp_name'], $dossier_cv . $nom_fichier);
                    $cv_path = 'uploads/cv/' . $nom_fichier;
                }

                $req = $pdo->prepare('INSERT INTO etudiant (id_utilisateur, id_etablissement, cv, formation) VALUES (?, ?, ?, ?)');
                $req->execute([$id_utilisateur, $id_etablissement, $cv_path, $formation]);
            }

            if ($role === 'medecin') {
                $id_etablissement_final = null;
                if ($id_etablissement_medecin === 'nouveau') {
                    // même logique anti-doublon que pour l'étudiant
                    $req = $pdo->prepare('SELECT id_etablissement FROM etablissement WHERE site_web = ?');
                    $req->execute([$site_etab]);
                    $existant = $req->fetch();
                    if ($existant) {
                        $id_etablissement_final = $existant['id_etablissement'];
                    } else {
                        $req = $pdo->prepare('INSERT INTO etablissement (nom, adresse, site_web) VALUES (?, ?, ?)');
                        $req->execute([$nom_etab, $adresse_etab, $site_etab]);
                        $id_etablissement_final = $pdo->lastInsertId();
                    }
                } elseif ($id_etablissement_medecin !== '') {
                    $id_etablissement_final = $id_etablissement_medecin;
                }
                $req = $pdo->prepare('INSERT INTO medecin (id_utilisateur, id_specialite, id_hopital, id_etablissement) VALUES (?, ?, ?, ?)');
                $req->execute([$id_utilisateur, $id_specialite, $id_hopital, $id_etablissement_final]);
            }

            if ($role === 'partenaire') {
                if ($id_entreprise === 'nouveau') {
                    // anti-doublon sur les entreprises
                    $req = $pdo->prepare('SELECT id_entreprise FROM entreprise WHERE site_web = ?');
                    $req->execute([$site_ent]);
                    $existant = $req->fetch();
                    if ($existant) {
                        $id_entreprise = $existant['id_entreprise'];
                    } else {
                        $req = $pdo->prepare('INSERT INTO entreprise (nom, adresse, site_web) VALUES (?, ?, ?)');
                        $req->execute([$nom_ent, $adresse_ent, $site_ent]);
                        $id_entreprise = $pdo->lastInsertId();
                    }
                }
                $req = $pdo->prepare('INSERT INTO partenaire (id_utilisateur, id_entreprise, poste) VALUES (?, ?, ?)');
                $req->execute([$id_utilisateur, $id_entreprise, $poste]);
            }

            $pdo->commit();

            // redirection vers la connexion avec un message de succès
            header('Location: connexion.php?inscription=ok');
            exit;

        } catch (Exception $e) {
            // annule toutes les insertions en cas d'erreur
            $pdo->rollBack();
            $erreurs[] = "Erreur lors de l'inscription : " . $e->getMessage();
        }
    }
}

// listes pour remplir les menus déroulants du formulaire
$hopitaux = $pdo->query('SELECT id_hopital, nom FROM hopital ORDER BY nom')->fetchAll();
$specialites = $pdo->query('SELECT id_specialite, libelle FROM specialite ORDER BY libelle')->fetchAll();
$etablissements = $pdo->query('SELECT id_etablissement, nom FROM etablissement ORDER BY nom')->fetchAll();
$entreprises = $pdo->query('SELECT id_entreprise, nom FROM entreprise ORDER BY nom')->fetchAll();
?>