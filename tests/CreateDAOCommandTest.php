<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Pool_CLI\Commands\CreateDAOCommand\CreateDAOCommand;

class CreateDAOCommandTest extends TestCase
{
    public function testCamelCaseClass(): void
    {
        $tableTest = 'user_details';
        $camelCaseClass = CreateDAOCommand::stringToCamelcase($tableTest, '_');
        $this->assertSame('UserDetails', $camelCaseClass, 'Table name should be camel cased');
    }
}