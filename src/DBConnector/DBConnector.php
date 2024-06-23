<?php
declare(strict_types=1);

namespace Pool_CLI\DBConnector;

use PDO;
use PDOException;

// todo: maybe mysqli is needed for production/dev?

readonly class DBConnector
{
    public function __construct(
        private string $host,
        private string $port,
        private string $user,
        private string $password,
    )
    {
    }

    public function connect(): PDO
    {
        try {
            $dsn = "mysql:host={$this->host};port={$this->port};}";
            $pdo = new PDO($dsn, $this->user, $this->password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $pdo;
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

}