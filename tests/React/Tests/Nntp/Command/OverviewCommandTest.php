<?php

namespace React\Tests\Nntp\Command;

use React\Nntp\Command\OverviewCommand;

class OverviewCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function commandExpectsMultilineResponse()
    {
        $command = new OverviewCommand($this->createStreamMock(), $this->createLoopMock(), 10, array());

        $this->assertTrue($command->expectsMultilineResponse());
    }

    /**
     * @test
     */
    public function commandShouldNotReturnInitialResult()
    {
        $command = new OverviewCommand($this->createStreamMock(), $this->createLoopMock(), 10, array());

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
