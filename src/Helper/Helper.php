<?php
declare(strict_types=1);

namespace Pool_CLI\Helper;

/**
 * Provides utility functions for the Pool CLI application.
 */
class Helper
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
     * @param Helper $helper An instance of the Helper class.
     * @param string $projectDir The directory of the project.
     * @param string $entity The entity or sub-namespace within the project.
     * @param string|null $suffix
     * @return string The fully qualified namespace.
     */
    public static function generateNamespace(
        Helper $helper,
        string $projectDir,
        string $entity,
        string $suffix = null
    ): string
    {
        $suffix = $suffix !== null ? '\\' . $suffix : '';
        $autoloadNamespacePrefix = $helper->getNamespacePrefix($projectDir);
        if ($autoloadNamespacePrefix !== '') {
            return $autoloadNamespacePrefix . $entity . $suffix;
        }
        return $projectDir . '\\' . $entity . $suffix;
    }
}