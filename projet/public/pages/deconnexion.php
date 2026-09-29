<?php
require __DIR__ . '/../includes/config.php';

session_unset(); // vide toutes les variables de session
session_destroy(); // détruit la session côté serveur

header('Location: connexion.php'); // redirige vers la page de connexion
exit;