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
}