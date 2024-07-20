<?php
declare(strict_types=1);

namespace Pool_CLI\Commands\CreateGUICommand;

use Pool_CLI\Helper\Helper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

use function Symfony\Component\String\u;

class CreateGUICommand extends Command
{
    protected function configure(): void
    {
        $this->setName('create:gui')
            ->addOption('no-style', 's', InputOption::VALUE_NONE, 'Dont create css file')
            ->addOption('no-script', 'j', InputOption::VALUE_NONE, 'Dont create js file')
            ->addOption('only-class', 'c', InputOption::VALUE_NONE, 'Just create class')
            ->setDescription('creates new GUI')
            ->setHelp('lookup on pool-documentation/pool-cli how to create new GUI');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('<info>Generate new GUI</info>');

        $projectDirs = Helper::getProjectDirs(SRC_DIR, 'guis');
        if (empty($projectDirs)) {
            $io->error('No project-folders with guis directory found');
            return Command::FAILURE;
        }
        $project = $io->choice('In which project you want to create a new GUI?', $projectDirs);
        $guiName = $io->askQuestion(new Question('Name of the GUI you want to create? No GUI_ needed'));

        $projectDir = SRC_DIR . '/' . $project;
        $fullGUI_DIR = $projectDir . '/guis/';
        $guiNameWithPrefix = 'GUI_' . u($guiName)->trim()->camel()->title()->ascii();
        $guiNameForHtml = u($guiName)->trim()->lower()->ascii()->snake();
        $guiDir = $fullGUI_DIR . '/' . $guiNameWithPrefix;

        $io->text('New GUI: ' . $guiNameWithPrefix . ' will be created in project: ' . $project);

        // create gui directory
        if (!$this->generateDirectory($guiDir, $io)) return Command::FAILURE;

        // create main GUI Class
        $dummyClass = file_get_contents(__DIR__ . '/Templates/GUI_Example.php');
        // TODO: how to proper handle namespace...?
        // TODO: how to add proper use statements?
        $mainClassContent = str_replace('_project_dir_', $project, $dummyClass);
        $mainClassContent = str_replace('GUI_Example', $guiNameWithPrefix, $mainClassContent);
        $mainClassContent = str_replace('tpl_example.html', 'tpl_' . $guiNameForHtml . '.html', $mainClassContent);
        if (!$this->generateFile(
            $guiDir . '/' . $guiNameWithPrefix . '.php',
            $mainClassContent,
            $io
        )) return Command::FAILURE;


        if ($input->getOption('only-class')) {
            $io->success("GUI class generated successfully (only-class)");
            return Command::SUCCESS;
        }

        // create dummy template
        $dummyTemplate = file_get_contents(__DIR__ . '/Templates/tpl_example.html');
        if (!$this->generateFile(
            $guiDir . '/' . 'tpl_' . $guiNameForHtml . '.html',
            $dummyTemplate,
            $io
        )) return Command::FAILURE;


        // create dummy javascript
        if ($input->getOption('no-script') === false) {
            $dummyJavascript = file_get_contents(__DIR__ . '/Templates/GUI_Example.js');
            if (!$this->generateFile(
                $guiDir . '/' . $guiNameWithPrefix . '.js',
                str_replace('GUI_Example', $guiNameWithPrefix, $dummyJavascript),
                $io
            )) return Command::FAILURE;
        }

        // create dummy css if not disabled
        if ($input->getOption('no-style') === false) {
            if (!$this->generateFile(
                $guiDir . '/' . $guiNameWithPrefix . '.css',
                '/* css for ' . $guiNameWithPrefix . ' */',
                $io
            )) return Command::FAILURE;
        }

        // create if not exits schemes directory
        if (!$this->isDir($projectDir . '/schemes')) $this->generateDirectory($projectDir . '/schemes', $io);
        // create schemes file
        if (!$this->generateFile(
            $projectDir . '/schemes/' . $guiNameForHtml->replace('_', '-') . '.html',
            "[\\$project\\guis\\$guiNameWithPrefix\\$guiNameWithPrefix]",
            $io
        )) return Command::FAILURE;

        $io->success("GUI generated successfully");
        return Command::SUCCESS;
    }

    private function isDir(string $dir): bool
    {
        return is_dir($dir);
    }

    private function generateDirectory(string $dir, SymfonyStyle $io): bool
    {
        if ($this->isDir($dir)) {
            $io->error("directory: $dir already exists");
            return false;
        }
        if (!mkdir($dir, 0755, true)) {
            $io->error("directory: $dir failed to create");
            return false;
        }
        return true;
    }

    private function generateFile(string $file, string $data, SymfonyStyle $io): bool
    {
        if (!file_put_contents($file, $data)) {
            $io->error("file: $file failed to create");
            return false;
        }
        return true;
    }
}
