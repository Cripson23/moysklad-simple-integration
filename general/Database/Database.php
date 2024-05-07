<?php

namespace general\Database;

use PDO;
use Exception;
use general\Logger;

/**
 * Класс управления подключением к БД
 */
class Database implements DatabaseInterface
{
    private PDO $conn;

    public function __construct(array $databaseConfig)
    {
        $dsn = $databaseConfig['driver']
            . ":host=" . $databaseConfig['host']
            . ";dbname=" . $databaseConfig['name']
            . ";charset=" . $databaseConfig['charset'];

        try {
            $this->conn = new PDO($dsn, $databaseConfig['user'], $databaseConfig['password']);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            Logger::logMessage("Ошибка подключения DB: " . $e->getMessage());
        }
    }

    public function getConnection(): PDO
    {
        return $this->conn;
    }
}