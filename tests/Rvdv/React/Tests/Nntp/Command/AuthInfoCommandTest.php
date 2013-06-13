<?php

namespace Rvdv\React\Tests\Nntp\Command;

use Phake;
use Rvdv\React\Nntp\Command\AuthInfoCommand;

class AuthInfoCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function commandExpectsMultilineResponse()
    {
        $stream = Phake::mock('React\Stream\Stream');

        $command = new AuthInfoCommand($stream, 'type', 'value');

        $this->assertFalse($command->expectsMultilineResponse());
    }

    /**
     * @test
     */
    public function commandShouldNotReturnInitialResult()
    {
        $stream = Phake::mock('React\Stream\Stream');

        $command = new AuthInfoCommand($stream, 'type', 'value');

        $this->assertNull($command->getResult());
    }
}
