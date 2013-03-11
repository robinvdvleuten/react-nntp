<?php

namespace React\Tests\Nntp\Command;

use React\Nntp\Command\OverviewFormatCommand;

class OverviewFormatCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function commandExpectsMultilineResponse()
    {
        $command = new OverviewFormatCommand($this->createStreamMock(), $this->createLoopMock());

        $this->assertTrue($command->expectsMultilineResponse());
    }

    /**
     * @test
     */
    public function commandShouldNotReturnInitialResult()
    {
        $command = new OverviewFormatCommand($this->createStreamMock(), $this->createLoopMock());

        $this->assertNull($command->getResult());
    }

    private function createLoopMock()
    {
        return $this->getMockBuilder('React\EventLoop\StreamSelectLoop')
                    ->disableOriginalConstructor()
                    ->getMock();
    }

    private function createStreamMock()
    {
        return $this->getMockBuilder('React\Stream\Stream')
                    ->disableOriginalConstructor()
                    ->getMock();
    }
}
