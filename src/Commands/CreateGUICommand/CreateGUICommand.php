<?php
declare(strict_types=1);

namespace Pool_CLI\Commands\CreateGUICommand;

use Pool_CLI\Helper\Helper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateGUICommand extends Command
{
    protected function configure(): void
    {
        $this->setName('create_gui')
            ->setDescription('creates new GUI')
            ->setHelp('help isnt needed :D');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('<info>Generate new GUI</info>');

        $projectDirs = Helper::getProjectDirs(SRC_DIR);
        $project = $io->choice('In which project you want to create a new GUI?', $projectDirs);
        $guiName = $io->askQuestion(new Question('Name of the GUI you want to create? No GUI_ needed'));

        $projectDir = SRC_DIR . '/' . $project;
        $fullGUI_DIR = $projectDir . '/guis/';
        $guiNameWithPrefix = 'GUI_' . $guiName;

        $io->text('New GUI: ' . $guiNameWithPrefix . ' will be created in project: ' . $project);

        // create gui directory
        $mkdirGUI = mkdir($fullGUI_DIR . '/' . $guiNameWithPrefix, 0755, true);
        if (!$mkdirGUI) {
            $io->error("directory failed to create");
            return Command::FAILURE;
        }
        // load dummy GUI Class to file
        $dummyClass = file_get_contents(__DIR__ . '/Templates/GUI_Example.php');

        // TODO: how to proper handle namespace...?
        // TODO: how to add proper use statements?
        $mainClassContent = str_replace('_project_dir_', $project, $dummyClass);
        $mainClassContent = str_replace('GUI_Example', $guiNameWithPrefix, $mainClassContent);
        $mainClassContent = str_replace('example', strtolower($guiName), $mainClassContent);

        // create main GUI Class
        $mainClass = file_put_contents($fullGUI_DIR . '/' . $guiNameWithPrefix . '/' . $guiNameWithPrefix . '.php', $mainClassContent);
        if (!$mainClass) {
            $io->error("main class file failed to create");
            return Command::FAILURE;
        }

        $dummyTemplate = file_get_contents(__DIR__ . '/Templates/tpl_example.html');
        $template = file_put_contents($fullGUI_DIR . '/' . $guiNameWithPrefix . '/' . 'tpl_' . strtolower($guiName) . '.html', $dummyTemplate);
        if (!$template) {
            $io->error("template file failed to create");
            return Command::FAILURE;
        }

        $dummyJavascript = file_get_contents(__DIR__ . '/Templates/GUI_Example.js');
        $javascriptFile = file_put_contents($fullGUI_DIR . '/' . $guiNameWithPrefix . '/' . $guiNameWithPrefix . '.js', str_replace('GUI_Example', $guiNameWithPrefix, $dummyJavascript));
        if (!$javascriptFile) {
            $io->error("javascript file failed to create");
            return Command::FAILURE;
        }

        $cssFile = file_put_contents($fullGUI_DIR . '/' . $guiNameWithPrefix . '/' . $guiNameWithPrefix . '.css', '/* css for ' . $guiNameWithPrefix . ' */');
        if (!$cssFile) {
            $io->error("css file failed to create");
            return Command::FAILURE;
        }

        // todo check if schema folder exists
        if (!is_dir($projectDir . '/schemes')) {
            $mkdirSchema = mkdir($projectDir . '/schemes', 0755, true);
            if (!$mkdirSchema) {
                $io->error("schema folder failed to create");
                return Command::FAILURE;
            }
        }

        $schemaFile = file_put_contents($projectDir . '/schemes/' . strtolower($guiName) . '.html', "[\\$project\\guis\\$guiNameWithPrefix\\$guiNameWithPrefix]");
        if (!$schemaFile) {
            $io->error("schemaFile failed to create");
            return Command::FAILURE;
        }

        $io->success("GUI generated successfully");
        return Command::SUCCESS;
    }

    /**
     * create array of directories in given dir
     *
     * @return array
     */
    private function getProjectDirs(): array
    {
        $projectDirs = [];
        $iterator = new \DirectoryIterator(SRC_DIR);
        foreach ($iterator as $items) {
            if ($items->isDir() && !$items->isDot()) {
                $projectDirs[] = $items->getFilename();
            }
        }
        return $projectDirs;
    }
}
