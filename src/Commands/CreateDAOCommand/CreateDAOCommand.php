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

        // DAO Folder as Const...? because all dao from all projects are in one place...
        /** OR this way..?
         * $projectDirs = $this->getProjectDirs();
         * $project = $io->choice('In which project you want to create a new GUI?', $projectDirs);
         * $projectDir = SRC_DIR . '/' . $project;
         * // create DAO folder
         * $mkdirGUI = mkdir($projectDir . '/dao', 0755, true);
         * if (!$mkdirGUI) {
         * $io->error("directory failed to create");
         * return Command::FAILURE;
         * }
         */

        // create DAO
        file_put_contents(DAO_DIR . "/$table.php", $this->generateDAO($columns, $table, $database));

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

    private function generateDAO(array $columns, string $table, string $database): string
    {

        // toodo only one loop
        // create pk, column array, and column commend

        $columnsArray = "";
        $columnsComment = "";
        $pk = "";

        foreach ($columns as $column) {
            $primaryKey = $column['Key'] === 'PRI' ? 'primaryKey' : '';
            $extra = $column['Extra'] !== '' ? "extra: {$column['Extra']}" : '';
            $default = ($column['Default'] === '' || $column['Default'] === NULL) ? '' : "default: {$column['Default']}";
            $columnsComment .= "\t * {$column['Field']} ({$column['Type']}) $default $extra $primaryKey\n";

            if ($column['Key'] === 'PRI') {
                $pk = "\tprivate string \$pk = '{$column['Field']}';\n";
            }

            $columnsArray .= "\t\t'{$column['Field']}',\n";
        }

        // todo make className camelCase
        $className = ucfirst($table);

        $data = "<?php\n";
        $data .= "declare(strict_types=1);\n\n";
        $data .= "//namespace ?\\daos;\n\n";
        $data .= "class $className\n";
        $data .= "{\n";
        $data .= "\tprotected string \$database = '$database';\n";
        $data .= "\tprotected string \$table = '$table';\n";
        $data .= $pk;

        $data .= "\t\n";

        $data .= "\t/**\n";
        $data .= "\t * columns of table $table\n";
        $data .= "\t *\n";
        $data .= $columnsComment;
        $data .= "\t */\n";

        $data .= "\tprivate array \$columns = [\n";
        $data .= $columnsArray;
        $data .= "\t];\n";

        $data .= "}\n";

        return $data;
    }
}
