<?php
declare(strict_types=1);

namespace Pool_CLI\Commands\CreateDAOCommand;

use Pool_CLI\DBConnector\DBConnector;
use PDO;
use Pool_CLI\Helper\Helper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function Symfony\Component\String\u;

/**
 * Command to create a new Data Access Object (DAO) for a specified table.
 *
 * This command guides the user through selecting a database connection, database, and table,
 * and then generates a PHP class that serves as a DAO for the selected table. The DAO includes
 * methods for basic CRUD operations and can be extended to include more complex business logic.
 */
class CreateDAOCommand extends Command
{
    /**
     * @var PDO The PDO instance for database connection.
     */
    private PDO $pdo;

    /**
     * Configures the command.
     * Sets the name, description, and help message for the command.
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('create:dao')
            ->setDescription('creates new DAO')
            ->setHelp('lookup on pool-documentation/pool-cli how to create new GUI');
    }

    /**
     * Executes the command.
     *
     * This method performs the command's primary function, including user interaction
     * to select database connection, database, table, and then generates the DAO class.
     *
     * @param InputInterface $input The input interface provided by Symfony Console.
     * @param OutputInterface $output The output interface provided by Symfony Console.
     * @return int Returns 0 on success, or an error code on failure.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('<info>Generate new DAO</info>');

        $dsn = $io->choice('Select DSN', array_keys(DATABASE_CONNECTIONS));
        $this->connect($dsn);
        $databases = $this->getDatabases($this->pdo);
        $database = $io->choice('Select Database', $databases);
        $tables = $this->getTables($this->pdo, $database);
        $table = $io->choice('Select Table', $tables);
        $columns = $this->getColumnsMeta($this->pdo, $table);
        $className = u($table)->trim()->camel()->ascii()->title()->toString();
        $fks = $this->getForeignKeys($this->pdo, $table, $database);
        $columns = $this->addForeignKeysInformation($columns, $fks);

        $projectDirs = Helper::getProjectDirs(SRC_DIR, 'daos');
        if (empty($projectDirs)) {
            $io->error('No project-folders with a daos directory found');
            return Command::FAILURE;
        }

        function setProjectDir($directories, $io, $path): string
        {
            if (in_array('daos', $directories)) {
                $path = $path . '/daos';
            } else {
                $directory = $io->choice('In which project you want to create a new GUI?', $directories);
                $path = $path . '/' . $directory;
            }
            $scan = scandir($path);
            $directoryHasDirectories = array_values(array_filter($scan, function ($dir) use ($path) {
                return is_dir($path . '/' . $dir) && $dir !== '.' && $dir !== '..';
            }));
            if (empty($directoryHasDirectories)) {
                return $path;
            }
            return setProjectDir($directoryHasDirectories, $io, $path);
        }

        $daoDirectory = setProjectDir($projectDirs, $io, SRC_DIR);

        $namespace = str_replace(SRC_DIR, '', $daoDirectory);
        $namespace = str_replace('/', '\\', $namespace);
        $namespace = ltrim($namespace, '\\');

        if (is_file($daoDirectory . "/$className.php")) {
            $io->error("DAO already exists");
            return Command::FAILURE;
        }

        $dao = file_put_contents(
            $daoDirectory . "/$className.php",
            $this->generateDAO($columns, $table, $database, $className, $namespace)
        );
        if (!$dao) {
            $io->error("dao failed to create");
            return Command::FAILURE;
        }

        $io->success("DAO generated successfully");
        return Command::SUCCESS;
    }

    /**
     * Establishes a database connection using the selected DSN.
     *
     * @param string $dsn The selected DSN for database connection.
     */
    private function connect(string $dsn): void
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

    /**
     * Retrieves a list of databases from the current database connection.
     *
     * @param PDO $pdo The PDO instance for database connection.
     * @return array An array of database names.
     */
    private function getDatabases(PDO $pdo): array
    {
        return $pdo->query('SHOW DATABASES')->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Retrieves a list of tables from the selected database.
     *
     * @param PDO $pdo The PDO instance for database connection.
     * @param string $database The name of the selected database.
     * @return array An array of table names within the selected database.
     */
    private function getTables(PDO $pdo, string $database): array
    {
        $pdo->query('USE ' . $database);
        return $pdo->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Retrieves metadata for all columns of the selected table.
     *
     * @param PDO $pdo The PDO instance for database connection.
     * @param string $table The name of the selected table.
     * @return array An associative array containing column metadata.
     */
    private function getColumnsMeta(PDO $pdo, string $table): array
    {
        return $pdo->query("SHOW FULL COLUMNS FROM $table")->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retrieves foreign key information for the selected table.
     *
     * @param PDO $pdo The PDO instance for database connection.
     * @param string $table The name of the selected table.
     * @param string $database The name of the selected database.
     * @return array An associative array containing foreign key details.
     */
    private function getForeignKeys(PDO $pdo, string $table, string $database): array
    {
        $stmt = $pdo->prepare("
            SELECT COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_NAME = :table 
            AND TABLE_SCHEMA = :database 
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        $stmt->execute([':table' => $table, ':database' => $database]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Adds foreign key information to the columns' metadata.
     *
     * This method enriches the columns metadata with foreign key details, facilitating
     * the generation of more complete DAO classes.
     *
     * @param array $columns The columns metadata.
     * @param array $fks The foreign keys information.
     * @return array The enriched columns metadata.
     */
    private function addForeignKeysInformation(array $columns, array $fks): array
    {
        foreach ($fks as $fk) {
            $index = 0;
            foreach ($columns as $column) {
                if ($column['Field'] === $fk['COLUMN_NAME']) {
                    $columns[$index]['ForeignKeys'][] = [
                        'constraint_name' => $fk['CONSTRAINT_NAME'],
                        'referenced_table_name' => $fk['REFERENCED_TABLE_NAME'],
                        'referenced_column_name' => $fk['REFERENCED_COLUMN_NAME'],
                    ];
                }
                $index++;
            }
        }
        return $columns;
    }

    /**
     * Generates the DAO class as a string.
     *
     * This method constructs the DAO class based on the provided metadata. It includes the namespace declaration,
     * class declaration, and properties for the database name, table name, primary keys, foreign keys, and columns.
     * The generated class extends a base DAO class and includes annotations for each column in the table.
     *
     * @param array $columns An associative array containing column metadata, including foreign key information.
     * @param string $table The name of the table for which the DAO is being generated.
     * @param string $database The name of the database containing the table.
     * @param string $className The name of the DAO class to be generated.
     * @param string $namespace The namespace under which the DAO class will be placed.
     * @return string The complete DAO class as a string, ready to be saved to a file.
     */
    private function generateDAO(
        array  $columns,
        string $table,
        string $database,
        string $className,
        string $namespace): string
    {
        $pk = ""; // array? more than one primary key?
        $fk = ""; // array of foreign keys
        $columnsComment = "";
        $columnsArray = "";

        foreach ($columns as $column) {
            $primaryKey = $column['Key'] === 'PRI' ? 'primaryKey' : '';
            $extra = $column['Extra'] !== '' ? "{$column['Extra']}" : '';
            $notNull = $column['Null'] === 'NO' ? 'NOT NULL' : '';
            $fkInfo = '';

            if (isset($column['ForeignKeys'])) {
                foreach ($column['ForeignKeys'] as $fkName) {
                    $fk .= "\t\t'{$fkName['constraint_name']}' => [\n";
                    $fk .= "\t\t\t'table' => '{$fkName['referenced_table_name']}',\n";
                    $fk .= "\t\t\t'column' => '{$fkName['referenced_column_name']}'\n";
                    $fk .= "\t\t]\n";
                    $fkInfo = "FOREIGN KEY ({$column['Field']}) REFERENCES {$fkName['referenced_table_name']}({$fkName['referenced_column_name']})";
                }
            }

            $columnsComment .= "\t * {$column['Field']} ({$column['Type']}) $notNull $extra $primaryKey $fkInfo\n";

            if ($column['Key'] === 'PRI') {
                $pk = "\tprotected array \$pk = [\n";
                $pk .= "\t\t'{$column['Field']}'\n";
                $pk .= "\t];\n";
            }

            $columnsArray .= "\t\t'{$column['Field']}',\n";
        }

        $fileData = "<?php\n";
        $fileData .= "declare(strict_types=1);\n\n";
        $fileData .= "namespace " . $namespace . ";\n\n";
        // todo:? --option from which Parent-DAO-Class to extend
        $fileData .= "use pool\classes\Database\DAO\MySQL_DAO;\n\n";
        $fileData .= "class $className extends MySQL_DAO\n";
        $fileData .= "{\n";
        $fileData .= "\tprotected static ?string \$databaseName = '$database';\n";
        $fileData .= "\tprotected static ?string \$tableName = '$table';\n";
        $fileData .= $pk;
        if ($fk) {
            $fileData .= "\tprotected array \$fk = [\n";
            $fileData .= $fk;
            $fileData .= "\t];\n";
        }
        $fileData .= "\n";
        $fileData .= "\t/**\n";
        $fileData .= "\t * columns of table $table\n";
        $fileData .= "\t *\n";
        $fileData .= $columnsComment;
        $fileData .= "\t */\n";
        $fileData .= "\tprotected array \$columns = [\n";
        $fileData .= $columnsArray;
        $fileData .= "\t];\n";
        $fileData .= "}\n";

        return $fileData;
    }
}
