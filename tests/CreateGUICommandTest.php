<?php
declare(strict_types=1);

require_once __DIR__ . '/TestUtils.php';

use PHPUnit\Framework\TestCase;
use Pool_CLI\Commands\CreateGUICommand\CreateGUICommand;
use Symfony\Component\Console;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Tester\CommandTester;

final class CreateGUICommandTest extends TestCase
{
    use TestUtils;

    /** @var Command|null */
    protected ?Command $command = null;

    /** @var CommandTester|null */
    protected ?CommandTester $tester = null;

    protected function setUp(): void
    {
        $this->command = new Command('create:gui');
        $this->tester = new CommandTester($this->command);
    }

    protected function tearDown(): void
    {
        $this->command = null;
        $this->tester = null;
    }

    public function testCreateGUI(): void
    {
        $foo = $this->tester->execute([], []);
        var_dump($foo);
    }

}

class SymfonyStyleWithForcedLineLength extends SymfonyStyle
{
    /**
     * @throws ReflectionException
     */
    public function __construct(InputInterface $input, OutputInterface $output)
    {
        parent::__construct($input, $output);
        $ref = new \ReflectionProperty(get_parent_class($this), 'lineLength');
//        $ref->setAccessible(true);
        $ref->setValue($this, 120);
    }
}
