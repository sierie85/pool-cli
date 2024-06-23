<?php

declare(strict_types=1);

namespace Pool_CLI\Commands\ShowSchemesCommand;

use Pool_CLI\Helper\Helper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class ShowSchemesCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('show_routes')
            ->setDescription('show all routes via schemes/ folder')
            ->setHelp('help isnt needed :D');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('<info>Show routes of Project</info>');

        $projectDirs = Helper::getProjectDirs(SRC_DIR);
        $project = $io->choice('Of which project you want to see all routes?', $projectDirs);
        $projectDir = SRC_DIR . '/' . $project;
        
        return Command::SUCCESS;
    }
}
