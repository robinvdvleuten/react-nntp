<?php

namespace React\Tests\Nntp\Command;

use React\Nntp\Command\AuthInfoCommand;

class AuthInfoCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function executeShouldReturnCorrectString()
    {
        $command = new AuthInfoCommand('type', 'value');

        $this->assertRegExp('/^AUTHINFO type value$/', $command->execute());
    }

    /**
     * @test
     */
    public function commandExpectsMultilineResponse()
    {
        $command = new AuthInfoCommand('type', 'value');

        $this->assertFalse($command->expectsMultilineResponse());
    }

    /**
     * @test
     */
    public function commandShouldNotReturnResult()
    {
        $command = new AuthInfoCommand('type', 'value');

        $this->assertNull($command->getResult());
    }
}
