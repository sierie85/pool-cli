<?php
declare(strict_types=1);

namespace Pool_CLI\Commands\ListAjaxRequestsCommand;

use Pool_CLI\Helper\Helper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ListAjaxRequestsCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('list_ajax_requests')
            ->setDescription('show all ajax_requests from guis in chosen project')
            ->setHelp('lookup on pool-documentation/pool-cli how to list all ajax_requests from GUIs in project');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('<info>Show all ajax_requests of Project</info>');

        $projectDirs = Helper::getProjectDirs(SRC_DIR, 'guis');
        $project = $io->choice('Of which project you want to see all ajax_requests?', $projectDirs);
        $projectDir = SRC_DIR . '/' . $project . '/guis';

        $ajaxRequests = [];
        $dirIterator = new \DirectoryIterator($projectDir);
        foreach ($dirIterator as $items) {
            if ($items->isDir() && !$items->isDot()) {
                $fileIterator = new \DirectoryIterator($projectDir . '/' . $items->getFilename());
                foreach ($fileIterator as $item) {
                    // todo: get all php files? is this necessary? none gui load ajax?
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
