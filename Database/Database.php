<?php
namespace MA\PHPQUICK\Database;

use PDO;
use Exception;
use PDOException;

class Database
{
    protected static ?PDO $pdo = null;
    protected array $dbconfig;
    protected string $username;
    protected string $password;

    public function __construct(array $dbconfig, string $username = 'root', string $password = '')
    {
        $this->dbconfig = $dbconfig;
        $this->username = $username;
        $this->password = $password;

        if (self::$pdo === null) {
            self::$pdo = $this->initialize();
        }
    }

    private function initialize(): PDO
    {
        $db = $this->dbconfig;

        if (!$db) {
            throw new Exception('Config database not found', 500);
        }

        $dsn = sprintf(
            "%s:host=%s;port=%s;dbname=%s;charset=%s",
            $db['driver'],
            $db['host'],
            $db['port'],
            $db['dbname'],
            $db['charset']
        );

        try {
            return new PDO($dsn, $this->username, $this->password);
        } catch (PDOException $e) {
            throw new Exception('Koneksi ke basis data gagal: ' . $e->getMessage(), 500);
        }
    }

    public function query(string $query, ?array $params = null): \PDOStatement|false
    {
        $statement = self::$pdo->prepare($query);
        
        $statement->execute($params);

        return $statement;
    }

    public static function getConnection(): PDO
    {
        return self::$pdo ??= (new static(config('database'), config('database.username'), config('database.password')))->initialize();
    }

    public static function beginTransaction(): void
    {
        self::getConnection()->beginTransaction();
    }

    public static function commitTransaction(): void
    {
        self::getConnection()->commit();
    }

    public static function rollbackTransaction(): void
    {
        self::getConnection()->rollBack();
    }
}
