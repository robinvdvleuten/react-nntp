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

    /**
     * @test
     */
    public function responseMessageShouldBeConvertedToObject()
    {
        $command = new GroupCommand($this->createStreamMock(), $this->createLoopMock(), 'test');

        $response = $this->getMock('React\Nntp\Response\ResponseInterface');
        $response->expects($this->once())
            ->method('getMessage')
            ->will($this->returnValue('10 5 15 group_name'))
        ;

        $command->handleGroupSelectedResponse($response);
        $group = $command->getResult();

        $this->assertInstanceOf('React\Nntp\Group', $group);
        $this->assertEquals('group_name', $group->getName());
        $this->assertEquals(10, $group->getCount());
        $this->assertEquals(5, $group->getFirst());
        $this->assertEquals(15, $group->getLast());
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
