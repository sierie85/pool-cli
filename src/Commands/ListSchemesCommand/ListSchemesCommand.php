<?php

declare(strict_types=1);

namespace Pool_CLI\Commands\ListSchemesCommand;

use Pool_CLI\Helper\Helper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ListSchemesCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('list_schemes')
            ->setDescription('list all schemes(routes) in chosen project')
            ->setHelp('lookup on pool-documentation/pool-cli how to list all schemes in project');
    }

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
//                $routes[] = new TableSeparator();
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
