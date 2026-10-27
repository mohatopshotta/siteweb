<?php

/**
 * Connexion unique à la base de données (patron Singleton).
 *
 * Toutes les classes Manager récupèrent le PDO avec Database::getConnexion() :
 * la connexion n'est ouverte qu'une seule fois par page.
 * Les paramètres (BDD_HOTE, BDD_NOM, ...) sont définis dans public/includes/config.php.
 */
class Database
{
    private static ?PDO $connexion = null;

    // constructeur privé : on ne fait jamais "new Database()"
    private function __construct()
    {
    }

    public static function getConnexion(): PDO
    {
        if (self::$connexion === null) {
            $dsn = 'mysql:host=' . BDD_HOTE . ';dbname=' . BDD_NOM . ';charset=utf8mb4';
            try {
                self::$connexion = new PDO($dsn, BDD_UTILISATEUR, BDD_MOT_DE_PASSE, [
                    // lève une exception si une requête échoue
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    // résultats en tableaux associatifs
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
            } catch (PDOException $e) {
                die('Connexion à la base impossible : ' . $e->getMessage());
            }
        }
        return self::$connexion;
    }
}
