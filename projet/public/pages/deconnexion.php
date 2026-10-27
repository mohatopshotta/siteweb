<?php
require __DIR__ . '/../includes/config.php';

Session::deconnecter();
Session::rediriger('connexion.php');
