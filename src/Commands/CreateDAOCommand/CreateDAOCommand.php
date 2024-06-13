<?php
declare(strict_types=1);

namespace CLI_Pool\Commands\CreateDAOCommand;

use CLI_Pool\DBConnector\DBConnector;
use PDO;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateDAOCommand extends Command
{
    private PDO $pdo;

    protected function configure(): void
    {
        $this->setName('create_dao')
            ->setDescription('creates new DAO')
            ->setHelp('help isnt needed :D');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('<info>Generate new DAO</info>');

        $dsn = $io->choice('Select DSN', DATABASE_CONNECTIONS);
        $this->connect($dsn);
        $databases = $this->getDatabases($this->pdo);
        $database = $io->choice('Select Database', $databases);
        $tables = $this->getTables($this->pdo, $database);
        $table = $io->choice('Select Table', $tables);

        $columns = $this->getColumnsMeta($this->pdo, $table);

        var_dump($columns);

        // create DAO folder and DAO-file

        $io->success("DAO generated successfully");
        return Command::SUCCESS;
    }

    private function connect($dsn): void
    {
        $dbCredentials = DATABASE_CONNECTIONS[$dsn];
        $dbConnector = new DBConnector(
            $dbCredentials['host'],
            $dbCredentials['port'],
            $dbCredentials['user'],
            $dbCredentials['password'],
        );
        $this->pdo = $dbConnector->connect();
    }

    private function getDatabases(PDO $pdo): array
    {
        return $pdo->query('SHOW DATABASES')->fetchAll(\PDO::FETCH_COLUMN);
    }

    private function getTables(PDO $pdo, $database): array
    {
        $pdo->query('USE ' . $database);
        return $pdo->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);
    }

    private function getColumnsMeta(PDO $pdo, $table): array
    {
        return $pdo->query('SHOW FULL COLUMNS FROM ' . $table)->fetchAll(PDO::FETCH_ASSOC);
    }
}
