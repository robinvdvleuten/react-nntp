<?php

namespace React\Tests\Nntp\Command;

use React\Nntp\Command\GroupCommand;

class GroupCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function commandExpectsMultilineResponse()
    {
        $command = new GroupCommand($this->createStreamMock(), $this->createLoopMock(), 'test');

        $this->assertFalse($command->expectsMultilineResponse());
    }

    /**
     * @test
     */
    public function commandShouldNotReturnInitialResult()
    {
        $command = new GroupCommand($this->createStreamMock(), $this->createLoopMock(), 'test');

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
