<?php
declare(strict_types=1);

namespace Pool_CLI\Commands\ListAjaxRequestsCommand;

use Pool_CLI\Helper\Helper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command to list all registered Ajax methods for GUIs in a selected project.
 *
 * This command allows users to view all Ajax methods that have been registered
 * within the GUIs of a specified project. It scans the project directories for
 * PHP files that register Ajax methods and displays them in a formatted table.
 */
class ListAjaxRequestsCommand extends Command
{
    /**
     * Configures the command settings.
     * Sets the name, description, and help message for the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('list:ajax')
            ->setDescription('show all registered ajaxMethods from guis in chosen project')
            ->setHelp('lookup on pool-documentation/pool-cli how to list all ajaxMethods from GUIs in project');
    }

    /**
     * Executes the command to list all Ajax methods.
     *
     * @param InputInterface $input The input interface.
     * @param OutputInterface $output The output interface.
     * @return int The status code (0 for success, non-zero for failure).
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('<info>Show all registered AjaxMethods of Project</info>');

        $projectDirs = Helper::getProjectDirs(SRC_DIR, 'guis');
        $project = $io->choice('Of which project you want to see all ajaxMethods?', $projectDirs);
        $projectDir = SRC_DIR . '/' . $project . '/guis';

        $ajaxRequests = [];
        $dirIterator = new \DirectoryIterator($projectDir);
        foreach ($dirIterator as $items) {
            if ($items->isDir() && !$items->isDot()) {
                $fileIterator = new \DirectoryIterator($projectDir . '/' . $items->getFilename());
                foreach ($fileIterator as $item) {
                    if ($item->isFile() && $item->getExtension() === 'php' && str_starts_with($item->getFilename(), 'GUI_')) {
                        $file = file_get_contents($projectDir . '/' . $items->getFilename() . '/' . $item->getFilename());
                        $matches = [];
                        preg_match_all('/\$this->registerAjaxMethod\((.*?[);])/', $file, $matches);
                        foreach ($matches[1] as $match) {
                            $matchArray = explode(',', $match);
                            $handler = trim($matchArray[0], '\'"');
                            $method = trim($matchArray[1]);
                            $ajaxRequests[] = [
                                'gui' => $items->getFilename(),
                                'uri' => "?module={$items->getFilename()}&method={$handler}",
                                'handler' => $handler,
                                'method' => $method
                            ];
                        }
                    }
                }
            }
        }

        $table = new Table($output);
        $table->setHeaders(['GUI', 'url-query', 'js-handler-name', 'php-method']);
        $table->setRows($ajaxRequests);
        $table->setStyle('box-double');
        $table->render();

        return Command::SUCCESS;
    }
}
