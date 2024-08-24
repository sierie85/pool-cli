<?php

declare(strict_types=1);

namespace Pool_CLI\DBConnector;

use PDO;
use PDOException;

/**
 * A class to handle database connections using PDO.
 *
 * This class is designed to establish a connection to a MySQL database using PDO.
 * It encapsulates the connection details (host, port, user, password) and provides
 * a method to connect to the database. The connection is configured to throw exceptions
 * on error and to fetch data as associative arrays by default.
 *
 * Note: Consideration for mysqli extension usage is suggested for specific production or development needs.
 */
readonly class DBConnector
{
    /**
     * Constructor to initialize the database connection parameters.
     *
     * @param string $host Database host address.
     * @param string $port Database port number.
     * @param string $user Database user name.
     * @param string $password Database password.
     */
    public function __construct(
        private string $host,
        private string $port,
        private string $user,
        private string $password,
    ) {}

    /**
     * Establishes a PDO connection to the database.
     *
     * This method attempts to create a new PDO connection using the provided
     * credentials and configuration. It sets error handling to exception mode
     * and the default fetch mode to associative array. If the connection fails,
     * the method terminates the script and outputs the error message.
     *
     * @return PDO The PDO connection object on success.
     */
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