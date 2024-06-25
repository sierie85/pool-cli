<?php
declare(strict_types=1);

namespace Pool_CLI\Helper;

class Helper
{
    /**
     *
     * @param string $sourceDir
     * @param string $indicator
     * @return array
     */
    public static function getProjectDirs(string $sourceDir, string $indicator): array
    {
        $projectDirs = [];
        $dirIterator = new \DirectoryIterator($sourceDir);
        foreach ($dirIterator as $items) {
            if ($items->isDir() && !$items->isDot()) {
                $folderIterator = new \DirectoryIterator($sourceDir . '/' . $items->getFilename());
                $isProject = false;
                foreach ($folderIterator as $folder) {
                    if ($folder->isDir() && !$folder->isDot() && $folder->getFilename() === $indicator) {
                        $isProject = true;
                    }
                }
                if ($isProject) {
                    $projectDirs[] = $items->getFilename();
                }
            }
        }
        return $projectDirs;
    }
}