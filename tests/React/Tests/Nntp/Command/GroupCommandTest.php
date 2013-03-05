<?php

namespace React\Tests\Nntp\Command;

use React\Nntp\Command\GroupCommand;

class GroupCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function executeShouldReturnCorrectString()
    {
        $command = new GroupCommand('test');

        $this->assertRegExp('/^GROUP test$/', $command->execute());
    }

    /**
     * @test
     */
    public function commandExpectsMultilineResponse()
    {
        $command = new GroupCommand('test');

        $this->assertFalse($command->expectsMultilineResponse());
    }
}
