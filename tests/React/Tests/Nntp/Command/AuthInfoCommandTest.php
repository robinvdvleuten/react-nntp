<?php

namespace React\Tests\Nntp\Command;

use React\Nntp\Command\AuthInfoCommand;

class AuthInfoCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function commandExpectsMultilineResponse()
    {
        $command = new AuthInfoCommand($this->createStreamMock(), $this->createLoopMock(), 'type', 'value');

        $this->assertFalse($command->expectsMultilineResponse());
    }

    /**
     * @test
     */
    public function commandShouldNotReturnInitialResult()
    {
        $command = new AuthInfoCommand($this->createStreamMock(), $this->createLoopMock(), 'type', 'value');

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
