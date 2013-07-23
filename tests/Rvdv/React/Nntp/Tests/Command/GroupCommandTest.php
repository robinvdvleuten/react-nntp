<?php

/*
 * This file is part of the React NNTP component.
 *
 * (c) Robin van der Vleuten <robinvdvleuten@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rvdv\React\Nntp\Tests\Command;

use Phake;
use Rvdv\React\Nntp\Command\GroupCommand;
use Rvdv\React\Nntp\Response\ResponseInterface;

/**
 * GroupCommandTest
 *
 * @author Robin van der Vleuten <robinvdvleuten@gmail.com>
 */
class GroupCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testCommandExpectsMultilineResponse()
    {
        $stream = Phake::mock('React\Stream\Stream');

        $command = new GroupCommand($stream, 'test');

        $this->assertFalse($command->expectsMultilineResponse());
    }

    public function testCommandShouldNotReturnInitialResult()
    {
        $stream = Phake::mock('React\Stream\Stream');

        $command = new GroupCommand($stream, 'test');

        $this->assertNull($command->getResult());
    }

    public function testResponseMessageShouldBeConvertedToObject()
    {
        $stream = Phake::mock('React\Stream\Stream');

        $command = new GroupCommand($stream, 'test');

        $response = Phake::mock('Rvdv\React\Nntp\Response\ResponseInterface');

        Phake::when($response)->getMessage()->thenReturn('10 5 15 group_name');

        $command->handleGroupSelectedResponse($response);
        $group = $command->getResult();

        Phake::verify($response, Phake::times(1))->getMessage();

        $this->assertInstanceOf('Rvdv\React\Nntp\Group', $group);
        $this->assertEquals('group_name', $group->getName());
        $this->assertEquals(10, $group->getCount());
        $this->assertEquals(5, $group->getFirst());
        $this->assertEquals(15, $group->getLast());
    }

    public function testCommandExecutesCorrectGroupName()
    {
        $stream = Phake::mock('React\Stream\Stream');

        $command = new GroupCommand($stream, 'test');
        $command->execute();

        Phake::verify($stream, Phake::times(1))->write("GROUP test\r\n");
    }

    public function testResultIsHandledByCorrectHandler()
    {
        $stream = Phake::mock('React\Stream\Stream');

        $command = new GroupCommand($stream, 'test');

        $response = Phake::mock('Rvdv\React\Nntp\Response\ResponseInterface');

        Phake::when($response)->getStatusCode()->thenReturn(ResponseInterface::GROUP_SUCCESSFULLY_SELECTED);
        Phake::when($response)->getMessage()->thenReturn('10 5 15 group_name');

        $command->handleResponse($response);

        $response = Phake::mock('Rvdv\React\Nntp\Response\ResponseInterface');

        Phake::when($response)->getStatusCode()->thenReturn(ResponseInterface::NO_SUCH_NEWSGROUP);

        $this->setExpectedException('Rvdv\React\Nntp\Exception\BadResponseException');

        $command->handleResponse($response);
    }
}
