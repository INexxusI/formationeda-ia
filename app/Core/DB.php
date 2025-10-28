<?php
namespace Core;

use PDO;
use PDOException;

class DB {
    private static ?PDO $pdo = null;

    public static function pdo(): PDO {
        if (self::$pdo) return self::$pdo;

        // On lit la config à chaque boot (léger et sûr)
        $config = require BASE_PATH . '/config/app.php';
        $host = $config['db']['host'] ?? 'localhost';
        $port = $config['db']['port'] ?? 3306;
        $name = $config['db']['name'] ?? 'formationeda_ia';
        $user = $config['db']['user'] ?? 'francois_ia';
        $pass = $config['db']['pass'] ?? '';

        try {
            self::$pdo = new PDO(
                "mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4",
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        } catch (PDOException $e) {
            die('Erreur de connexion DB : ' . $e->getMessage());
        }
        return self::$pdo;
    }
}
