<?php

namespace Rvdv\React\Tests\Nntp\Command;

use Phake;
use Rvdv\React\Nntp\Command\OverviewFormatCommand;

class OverviewFormatCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function commandExpectsMultilineResponse()
    {
        $stream = Phake::mock('React\Stream\Stream');

        $command = new OverviewFormatCommand($stream);

        $this->assertTrue($command->expectsMultilineResponse());
    }

    /**
     * @test
     */
    public function commandShouldNotReturnInitialResult()
    {
        $stream = Phake::mock('React\Stream\Stream');

        $command = new OverviewFormatCommand($stream);

        $this->assertNull($command->getResult());
    }
}
