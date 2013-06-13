<?php

namespace Rvdv\React\Tests\Nntp\Command;

use Phake;
use Rvdv\React\Nntp\Command\OverviewCommand;

class OverviewCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function commandExpectsMultilineResponse()
    {
        $stream = Phake::mock('React\Stream\Stream');

        $command = new OverviewCommand($stream, 10, array());

        $this->assertTrue($command->expectsMultilineResponse());
    }

    /**
     * @test
     */
    public function commandShouldNotReturnInitialResult()
    {
        $stream = Phake::mock('React\Stream\Stream');

        $command = new OverviewCommand($stream, 10, array());

        $this->assertNull($command->getResult());
    }
}
