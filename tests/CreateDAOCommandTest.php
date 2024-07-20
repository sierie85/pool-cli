<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Pool_CLI\Commands\CreateDAOCommand\CreateDAOCommand;

use function Symfony\Component\String\u;

class CreateDAOCommandTest extends TestCase
{
    public function testCamelCaseClass(): void
    {
        $tableTest = 'user_details';
        $camelCaseClass = u($tableTest)->trim()->camel()->ascii()->toString();
        $this->assertSame('UserDetails', $camelCaseClass, 'Table name should be camel cased');
    }
}