<?php

declare(strict_types=1);

namespace Pool_CLI\Commands\ListDAOviaGUICommand;

use Pool_CLI\Utils\Utils;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ListDAOviaGUICommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('list:daos')
            ->setDescription('show all DAO(s) used in guis of chosen project')
            ->setHelp('lookup on pool-documentation/pool-cli how to list all GUIs in project');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('<info>Show all DAO(s) used in GUI(s) of chosen Project</info>');

        $projectDirs = Utils::getProjectDirs(SRC_DIR, 'guis');
        $project = $io->choice('Select Project:', $projectDirs);
        $projectDir = SRC_DIR . '/' . $project . '/guis';

        $listOfDAOs = [];
        $dirIterator = new \DirectoryIterator($projectDir);
        foreach ($dirIterator as $items) {
            if ($items->isDir() && !$items->isDot()) {
                $fileIterator = new \DirectoryIterator($projectDir . '/' . $items->getFilename());
                foreach ($fileIterator as $item) {
                    if ($item->isFile() && $item->getExtension() === 'php' && str_starts_with(
                            $item->getFilename(),
                            'GUI_',
                        )) {
                        $file = file_get_contents(
                            $projectDir . '/' . $items->getFilename() . '/' . $item->getFilename(),
                        );
                        $matches = [];
                        preg_match_all('/use (.*?DAO.*?);/i', $file, $matches);
                        foreach ($matches[1] as $match) {
                            $dao = $match;
                            if (!isset($listOfDAOs[$items->getFilename()])) {
                                $listOfDAOs[$items->getFilename()] = [
                                    'gui' => $items->getFilename(),
                                    'dao' => $dao,
                                ];
                            } else {
                                $listOfDAOs[$items->getFilename()]['dao'] .= "\n" . $dao;
                            }
                        }
                        if (count($matches[1]) > 0) {
                            $listOfDAOs[] = new TableSeparator();
                        }
                    }
                }
            }
        }
        array_pop($listOfDAOs);

        $table = new Table($output);
        $table->setHeaderTitle($project);
        $table->setHeaders(['GUI', 'DAO(s)']);
        $table->setRows($listOfDAOs);
        $table->setStyle('box-double');
        $table->render();

        return Command::SUCCESS;
    }
}
