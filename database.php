<?php
require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

class Database
{
    private static $cont = null;

    // Prevent instantiation
    private function __construct() {}

    public static function connect()
    {
        if (self::$cont === null) {
            // Load .env variables
            $dotenv = Dotenv::createImmutable(__DIR__);
            $dotenv->load();

            $dbName = $_ENV['DB_NAME'];
            $dbHost = $_ENV['DB_HOST'];
            $dbUsername = $_ENV['DB_USERNAME'];
            $dbUserPassword = $_ENV['DB_PASSWORD'];

            try {
                self::$cont = new PDO(
                    "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4",
                    $dbUsername,
                    $dbUserPassword,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
            } catch (PDOException $e) {
                die("Database connection failed: " . $e->getMessage());
            }
        }
        return self::$cont;
    }

    public static function disconnect()
    {
        self::$cont = null;
    }
}
?>