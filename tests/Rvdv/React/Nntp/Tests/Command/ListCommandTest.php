<?php

/*
 * This file is part of React NNTP.
 *
 * (c) Robin van der Vleuten <robinvdvleuten@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rvdv\React\Nntp\Tests\Command;

use Phake;
use Rvdv\React\Nntp\Command\ListCommand;
use Rvdv\React\Nntp\Response\ResponseInterface;

/**
 * ListCommandTest
 *
 * @author Robin van der Vleuten <robinvdvleuten@gmail.com>
 */
class ListCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @coverage \React\Nntp\Command\ListCommand::expectsMultilineResponse
     */
    public function testCommandExpectsMultilineResponse()
    {
        $stream = Phake::mock('React\Stream\Stream');

        $command = new ListCommand($stream);

        $this->assertTrue($command->expectsMultilineResponse());
    }

    /**
     * @coverage \React\Nntp\Command\ListCommand::getResult
     */
    public function testCommandShouldNotReturnInitialResult()
    {
        $stream = Phake::mock('React\Stream\Stream');

        $command = new ListCommand($stream);

        $this->assertNull($command->getResult());
    }

    public function testCommandShouldImplementAllResponseCodes()
    {
        $stream = Phake::mock('React\Stream\Stream');

        $command = new ListCommand($stream);

        $handlers = $command->getResponseHandlers();

        $this->assertArrayHasKey(ResponseInterface::GROUPS_FOLLOW, $handlers);
        $this->assertArrayHasKey(ResponseInterface::SYNTAX_ERROR, $handlers);
        $this->assertArrayHasKey(ResponseInterface::NOT_SUPPORTED, $handlers);
    }

    /**
     * @coverage \React\Nntp\Command\ListCommand::handleGroupsFollowResponse
     */
    public function testResponseMessageShouldBeConvertedToObject()
    {
        $stream = Phake::mock('React\Stream\Stream');

        $command = new ListCommand($stream);

        $response = Phake::mock('Rvdv\React\Nntp\Response\MultilineResponseInterface');

        Phake::when($response)->getLines()->thenReturn(array(
            'misc.test 3002322 3000234 y',
            'comp.risks 442001 441099 m',
            'alt.rfc-writers.recovery 4 1 y',
            'tx.natives.recovery 89 56 y',
            'tx.natives.recovery.d 11 9 n',
        ));

        $command->handleGroupsFollowResponse($response);
        $groups = $command->getResult();

        Phake::verify($response, Phake::times(1))->getLines();

        $this->assertContainsOnly('Rvdv\React\Nntp\Group', $groups);
        $this->assertCount(5, $groups);

        $group = reset($groups);
        $this->assertEquals('misc.test', $group->getName());
        $this->assertEquals(0, $group->getCount());
        $this->assertEquals(3000234, $group->getFirst());
        $this->assertEquals(3002322, $group->getLast());
        $this->assertTrue($group->getActive());
    }
}
