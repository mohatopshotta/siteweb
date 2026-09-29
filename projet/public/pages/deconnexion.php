<?php
require __DIR__ . '/../includes/config.php';

session_unset();
session_destroy();

header('Location: connexion.php');
exit;