<?php
declare(strict_types=1);

namespace CLI_Pool\DBConnector;

use PDO;
use PDOException;

class DBConnector
{
    public function __construct(
        private readonly string $host,
        private readonly string $port,
        private readonly string $user,
        private readonly string $password,
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