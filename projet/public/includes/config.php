<?php

session_start();

$dsn = 'mysql:host=localhost;dbname=hsp_gdh;charset=utf8mb4';
$bdd_utilisateur = 'root';
$bdd_mot_de_passe = '';

try {
    $pdo = new PDO($dsn, $bdd_utilisateur, $bdd_mot_de_passe, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('Connexion à la base impossible : ' . $e->getMessage());
} 