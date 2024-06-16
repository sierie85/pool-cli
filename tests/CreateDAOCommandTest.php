<?php
declare(strict_types=1);

use CLI_Pool\Commands\CreateDAOCommand\CreateDAOCommand;
use PHPUnit\Framework\TestCase;

class CreateDAOCommandTest extends TestCase
{
    public function testCamelCaseClass(): void
    {
        $tableTest = 'user_details';
        $camelCaseClass = CreateDAOCommand::stringToCamelcase($tableTest, '_');
        $this->assertSame('UserDetails', $camelCaseClass, 'Table name should be camel cased');
    }
}