<?php
class Database
{
    private static $dbName = 'einsbernproject'; // Ensure this matches your database name
    private static $dbHost = 'localhost';
    private static $dbUsername = 'einsbern'; // Update with your database username
    private static $dbUserPassword = 'einsbern.com'; // Update with your database password

    private static $cont = null;

    // Prevent instantiation
    private function __construct() {}

    public static function connect()
    {
        if (self::$cont === null) {
            try {
                self::$cont = new PDO(
                    "mysql:host=" . self::$dbHost . ";dbname=" . self::$dbName . ";charset=utf8mb4",
                    self::$dbUsername,
                    self::$dbUserPassword,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Enables exception mode for errors
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Fetches as associative array
                        PDO::ATTR_EMULATE_PREPARES => false, // Prevents SQL injection risk
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