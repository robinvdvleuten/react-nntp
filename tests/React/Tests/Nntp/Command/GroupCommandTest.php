<?php

/*
 * This file is part of the React NNTP component.
 *
 * (c) Robin van der Vleuten <robinvdvleuten@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace React\Tests\Nntp\Command;

use React\Nntp\Command\GroupCommand;
use React\Nntp\Response\ResponseInterface;

/**
 * GroupCommandTest
 *
 * @author Robin van der Vleuten <robinvdvleuten@gmail.com>
 */
class GroupCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testCommandExpectsMultilineResponse()
    {
        $command = new GroupCommand($this->createStreamMock(), 'test');

        $this->assertFalse($command->expectsMultilineResponse());
    }

    public function testCommandShouldNotReturnInitialResult()
    {
        $command = new GroupCommand($this->createStreamMock(), 'test');

        $this->assertNull($command->getResult());
    }

    public function testResponseMessageShouldBeConvertedToObject()
    {
        $command = new GroupCommand($this->createStreamMock(), 'test');

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

    public function testCommandExecutesCorrectGroupName()
    {
        $streamMock = $this->createStreamMock();

        $streamMock->expects($this->once())
            ->method('write')
            ->with($this->equalTo("GROUP test\r\n"));

        $command = new GroupCommand($streamMock, 'test');
        $command->execute();
    }

    public function testResultIsHandledByCorrectHandler()
    {
        $command = new GroupCommand($this->createStreamMock(), 'test');

        $response = $this->getMock('React\Nntp\Response\ResponseInterface');

        $response->expects($this->any())
            ->method('getStatusCode')
            ->will($this->returnValue(ResponseInterface::GROUP_SUCCESSFULLY_SELECTED))
        ;

        $response->expects($this->once())
            ->method('getMessage')
            ->will($this->returnValue('10 5 15 group_name'))
        ;

        $command->handleResponse($response);

        $response = $this->getMock('React\Nntp\Response\ResponseInterface');

        $response->expects($this->any())
            ->method('getStatusCode')
            ->will($this->returnValue(ResponseInterface::NO_SUCH_NEWSGROUP))
        ;

        $this->setExpectedException('React\Nntp\Exception\BadResponseException');

        $command->handleResponse($response);
    }

    private function createStreamMock()
    {
        return $this->getMockBuilder('React\Stream\Stream')
                    ->disableOriginalConstructor()
                    ->getMock();
    }
}
