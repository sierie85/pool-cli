<?php
declare(strict_types=1);

namespace Pool_CLI\Commands\ListSchemesCommand;

use Pool_CLI\Helper\Helper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command to list all schemes (routes) in a selected project.
 *
 * This command provides a list of all schemes (also known as routes) available within a specified project.
 * It checks the project's 'schemes' directory for HTML files that represent individual schemes and
 * extracts GUI components mentioned within these files. The command outputs a table listing each scheme and the GUI components it includes.
 */
class ListSchemesCommand extends Command
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
        $this->setName('list:schemes')
            ->setDescription('list all schemes(routes) in chosen project')
            ->setHelp('lookup on pool-documentation/pool-cli how to list all schemes in project');
    }

    /**
     * Executes the command to list all schemes (routes) in the selected project.
     *
     * @param InputInterface $input The input interface provided by Symfony Console.
     * @param OutputInterface $output The output interface provided by Symfony Console.
     * @return int Returns 0 on success, or an error code on failure.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('<info>List all schemes(routes) of Project</info>');

        $projectDirs = Helper::getProjectDirs(SRC_DIR, 'guis');
        $project = $io->choice('Of which project you want to see all schemes(routes)?', $projectDirs);
        $projectDir = SRC_DIR . '/' . $project . '/schemes';

        $routes = [];
        $fileIterator = new \DirectoryIterator($projectDir);
        foreach ($fileIterator as $items) {
            if ($items->isFile() && $items->getExtension() === 'html') {
                $route = pathinfo($items->getFilename(), PATHINFO_FILENAME);
                $content = htmlspecialchars(file_get_contents($projectDir . '/' . $items->getFilename()));
                preg_match_all('/\[(.*?)]/m', $content, $matches, PREG_SET_ORDER, 0);
                $guis = array_map(function ($gui) {
                    return $gui[0];
                }, $matches);

                $routes[$route]['url'] = "/?schema=" . $route;
                $routes[$route]['GUI'] = implode(',', $guis);
            }
        }

        $table = new Table($output);
        $table->setHeaders(['Route', 'GUI(s) included']);
        $table->setRows($routes);
        $table->setStyle('box-double');
        $table->render();

        return Command::SUCCESS;
    }
}
