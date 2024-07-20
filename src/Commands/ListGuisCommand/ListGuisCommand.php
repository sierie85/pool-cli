<?php
declare(strict_types=1);

namespace Pool_CLI\Commands\ListGuisCommand;

use Pool_CLI\Helper\Helper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command to list all GUIs in a selected project.
 *
 * This command provides a list of all GUIs (Graphical User Interfaces) available within a specified project.
 * It checks the project's 'guis' directory for any subdirectories that represent individual GUIs and
 * determines if they contain HTML templates. The command outputs a table listing each GUI and whether it has a template.
 */
class ListGuisCommand extends Command
{
    /**
     * Configures the command settings.
     * Sets the name, description, and help message for the command. This information is used by the console application
     * to display help and command lists.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('list:guis')
            ->setDescription('show all guis in chosen project')
            ->setHelp('lookup on pool-documentation/pool-cli how to list all GUIs in project');
    }

    /**
     * Executes the command to list all GUIs in the selected project.
     *
     * @param InputInterface $input The input interface provided by Symfony Console.
     * @param OutputInterface $output The output interface provided by Symfony Console.
     * @return int Returns 0 on success, or an error code on failure.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('<info>Show routes of Project</info>');

        $projectDirs = Helper::getProjectDirs(SRC_DIR, 'guis');
        $project = $io->choice('Of which project you want to see all GUIS?', $projectDirs);
        $projectDir = SRC_DIR . '/' . $project . '/guis';

        $guis = [];
        $dirIterator = new \DirectoryIterator($projectDir);
        foreach ($dirIterator as $items) {
            if ($items->isDir() && !$items->isDot()) {
                $fileIterator = new \DirectoryIterator($projectDir . '/' . $items->getFilename());
                $hasHtml = false;
                foreach ($fileIterator as $item) {
                    if ($item->isFile() && $item->getExtension() === 'html') {
                        $hasHtml = true;
                    }
                }
                $guis[$items->getFilename()]['gui'] = $items->getFilename();
                $guis[$items->getFilename()]['hasTemplate'] = $hasHtml ? 'yes' : 'no';
            }
        }

        $table = new Table($output);
        $table->setHeaders(['GUI', 'has template']);
        $table->setRows($guis);
        $table->setStyle('box-double');
        $table->render();

        return Command::SUCCESS;
    }
}
