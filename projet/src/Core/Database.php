<?php

class Database
{
    private static ?PDO $connexion = null;

    private function __construct()
    {
    }

    public static function getConnexion(): PDO
    {
        if (self::$connexion === null) {
            $dsn = 'mysql:host=' . BDD_HOTE . ';dbname=' . BDD_NOM . ';charset=utf8mb4';
            try {
                self::$connexion = new PDO($dsn, BDD_UTILISATEUR, BDD_MOT_DE_PASSE, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
            } catch (PDOException $e) {
                die('Connexion à la base impossible : ' . $e->getMessage());
            }
        }
        return self::$connexion;
    }
}
