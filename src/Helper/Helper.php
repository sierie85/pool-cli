<?php
declare(strict_types=1);

namespace Pool_CLI\Helper;

class Helper
{
    public static function getProjectDirs(string $sourceDir): array
    {
        $projectDirs = [];
        $iterator = new \DirectoryIterator($sourceDir);
        foreach ($iterator as $items) {
            if ($items->isDir() && !$items->isDot()) {
                $projectDirs[] = $items->getFilename();
            }
        }
        return $projectDirs;
    }
}