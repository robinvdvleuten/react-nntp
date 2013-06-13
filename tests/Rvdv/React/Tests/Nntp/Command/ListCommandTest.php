<?php

namespace Rvdv\React\Tests\Nntp\Command;

use Rvdv\React\Nntp\Command\ListCommand;
use Rvdv\React\Nntp\Response\ResponseInterface;

class ListCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @coverage \React\Nntp\Command\ListCommand::expectsMultilineResponse
     */
    public function testCommandExpectsMultilineResponse()
    {
        $command = new ListCommand($this->createStreamMock());

        $this->assertTrue($command->expectsMultilineResponse());
    }

    /**
     * @coverage \React\Nntp\Command\ListCommand::getResult
     */
    public function testCommandShouldNotReturnInitialResult()
    {
        $command = new ListCommand($this->createStreamMock());

        $this->assertNull($command->getResult());
    }

    public function testCommandShouldImplementAllResponseCodes()
    {
        $command = new ListCommand($this->createStreamMock());

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
        $command = new ListCommand($this->createStreamMock());

        $response = $this->getMock('Rvdv\React\Nntp\Response\MultilineResponseInterface');

        $response->expects($this->once())
            ->method('getLines')
            ->will($this->returnValue([
                'misc.test 3002322 3000234 y',
                'comp.risks 442001 441099 m',
                'alt.rfc-writers.recovery 4 1 y',
                'tx.natives.recovery 89 56 y',
                'tx.natives.recovery.d 11 9 n',
            ]));

        $command->handleGroupsFollowResponse($response);
        $groups = $command->getResult();

        $this->assertContainsOnly('Rvdv\React\Nntp\Group', $groups);
        $this->assertCount(5, $groups);

        $group = reset($groups);
        $this->assertEquals('misc.test', $group->getName());
        $this->assertEquals(0, $group->getCount());
        $this->assertEquals(3000234, $group->getFirst());
        $this->assertEquals(3002322, $group->getLast());
        $this->assertTrue($group->getActive());
    }

    private function createStreamMock()
    {
        return $this->getMockBuilder('React\Stream\Stream')
                    ->disableOriginalConstructor()
                    ->getMock();
    }
}
