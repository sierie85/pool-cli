<?php

declare(strict_types=1);

namespace Pool_CLI\Utils;

use Symfony\Component\Console\Application;
use Symfony\Component\Yaml\Yaml;

/**
 * Provides utility functions for the Pool CLI application.
 */
class Utils
{
    /**
     * Retrieves directories within a specified source directory that contain a specific indicator.
     *
     * This method scans the given source directory for subdirectories. It then checks each subdirectory
     * to see if it contains a further subdirectory named according to the provided indicator. This is used,
     * for example, to find project directories that contain a 'guis' or 'schemes' subdirectory, indicating
     * that they are relevant to certain commands within the application.
     *
     * @param string $sourceDir The directory to scan for project directories.
     * @param string $indicator The name of the subdirectory that indicates a relevant project directory.
     * @return array An array of directory names that contain the indicator.
     */
    public static function getProjectDirs(string $sourceDir, string $indicator): array
    {
        $projectDirs = [];
        foreach (new \DirectoryIterator($sourceDir) as $dir) {
            if ($dir->isDir() && !$dir->isDot() && is_dir("{$dir->getPathname()}/{$indicator}")) {
                $projectDirs[] = $dir->getFilename();
            }
        }
        return $projectDirs;
    }

    /**
     * Retrieves the namespace prefix from the composer autoload configuration.
     *
     * This method loads the composer autoload file from a specified project directory
     * and extracts the namespace prefix for PSR-4 autoloaded classes. This prefix is used
     * to construct fully qualified class names dynamically.
     *
     * @param string $projectDir The directory of the project from which to load the composer autoload file.
     * @return string The namespace prefix, or an empty string if not found.
     */
    private function getNamespacePrefix(string $projectDir): string
    {
        if (!file_exists(SRC_DIR . '/' . $projectDir . '/vendor/autoload.php')) {
            return '';
        }
        $composerAutoload = require SRC_DIR . '/' . $projectDir . '/vendor/autoload.php';
        $prefixes = $composerAutoload->getPrefixesPsr4();
        foreach ($prefixes as $namespace => $directories) {
            foreach ($directories as $directory) {
                if (str_starts_with(SRC_DIR, $directory)) {
                    return $namespace;
                }
            }
        }
        return '';
    }

    /**
     * Generates a fully qualified namespace for a class within a project.
     *
     * This method uses the namespace prefix determined by `getNamespacePrefix` and appends
     * the specified entity and class name to generate a fully qualified namespace. This is useful
     * for dynamically referencing classes within a project.
     *
     * @param Utils $utils An instance of the Helper class.
     * @param string $projectDir The directory of the project.
     * @param string $entity The entity or sub-namespace within the project.
     * @param string|null $suffix
     * @return string The fully qualified namespace.
     */
    public static function generateNamespace(
        Utils $utils,
        string $projectDir,
        string $entity,
        string $suffix = null,
    ): string {
        $suffix = $suffix !== null ? '\\' . $suffix : '';
        $autoloadNamespacePrefix = $utils->getNamespacePrefix($projectDir);
        if ($autoloadNamespacePrefix !== '') {
            return $autoloadNamespacePrefix . $entity . $suffix;
        }
        return $projectDir . '\\' . $entity . $suffix;
    }

    /**
     * Loads the configuration file and sets up necessary constants.
     *
     * This method attempts to load a YAML configuration file from the project root directory.
     * It validates the presence of required configuration keys and defines constants based on the configuration.
     *
     * @param string|null $binDir The directory of the binary, used to determine the project root.
     * @return void
     */
    public static function loadConfig(string|null $binDir = null): void
    {
        $projectRoot = self::getProjectRootDir($binDir);
        try {
            $config = Yaml::parseFile(self::lookupForConfigFile($projectRoot));
            self::validateConfigKey("source_directory", $config);
            define('SRC_DIR', $projectRoot . '/' . $config["source_directory"]);
            self::validateConfigKey("database_connections", $config);
            define('DATABASE_CONNECTIONS', $config["database_connections"]);
            if (isset($config["external_commands_directory"])) {
                define('EXTERNAL_COMMANDS_DIR', $projectRoot . '/' . $config["external_commands_directory"]);
            }
        } catch (\Exception $e) {
            echo "Error: Could not load configuration file. Please ensure that config-pool-cli.yaml exists in the root or config directory.";
            exit(1);
        }
    }

    /**
     * Determines the project root directory.
     *
     * This method calculates the project root directory based on the provided binary directory.
     * If no binary directory is provided, it defaults to two levels up from the current directory.
     *
     * @param string|null $binDir The directory of the binary.
     * @return string The project root directory.
     */
    private static function getProjectRootDir(string|null $binDir): string
    {
        if (isset($binDir)) {
            return dirname($binDir, 2);
        }
        return dirname(__DIR__, 2);
    }

    /**
     * Validates the presence of a configuration key.
     *
     * This method checks if a specific key is present in the configuration array.
     * If the key is missing, it outputs an error message and terminates the script.
     *
     * @param string $configKey The configuration key to validate.
     * @param array $config The configuration array.
     * @return void
     */
    private static function validateConfigKey(string $configKey, array $config): void
    {
        if (!isset($config[$configKey])) {
            echo "Error: The database_connections key is missing from the configuration file.";
            exit(1);
        }
    }

    /**
     * Looks up the configuration file in the project root directory.
     *
     * This method checks for the existence of the configuration file in the project root directory.
     * If the file does not exist, it attempts to generate a default configuration file.
     *
     * @param string $projectRoot The project root directory.
     * @return string The path to the configuration file.
     */
    private static function lookupForConfigFile(string $projectRoot): string
    {
        if (file_exists($projectRoot . '/config/config-pool-cli.yaml')) {
            return $projectRoot . '/config/config-pool-cli.yaml';
        }
        if (!file_exists($projectRoot . '/config-pool-cli.yaml')) {
            self::generateConfigFile($projectRoot);
        }
        return $projectRoot . '/config-pool-cli.yaml';
    }

    /**
     * Generates a default configuration file.
     *
     * This method creates a default YAML configuration file in the project root directory.
     * The generated file includes default values for source directory, external commands directory, and database connections.
     *
     * @param string $projectRoot The project root directory.
     * @return void
     */
    private static function generateConfigFile(string $projectRoot): void
    {
        echo 'Try to generate config file in project root: ' . $projectRoot . '/config-pool-cli.yaml' . PHP_EOL;
        $config = [
            'source_directory' => 'src',
            'external_commands_directory' => 'src/commands',
            'database_connections' => [
                'default' => [
                    'host' => 'localhost',
                    'port' => '3306',
                    'user' => 'user',
                    'password' => 'password',
                ],
            ],
        ];
        file_put_contents($projectRoot . '/config-pool-cli.yaml', Yaml::dump($config));
    }

    /**
     * Loads external commands into the application.
     *
     * This method scans the external commands directory for command subdirectories.
     * It then loads and adds each command class to the application.
     *
     * @param Application $app The Symfony Console application instance.
     * @return void
     */
    public static function loadExternalCommands(Application $app): void
    {
        $externalCommands = scandir(EXTERNAL_COMMANDS_DIR);
        foreach ($externalCommands as $externalCommand) {
            if (is_dir(EXTERNAL_COMMANDS_DIR . '/' . $externalCommand) && str_contains($externalCommand, 'Command')) {
                $externalCommandFile = EXTERNAL_COMMANDS_DIR . '/' . $externalCommand . '/' . $externalCommand . '.php';
                if (file_exists($externalCommandFile)) {
                    require_once $externalCommandFile;
                    $commandClass = $externalCommand;
                    $app->add(new $commandClass());
                }
            }
        }
    }
}