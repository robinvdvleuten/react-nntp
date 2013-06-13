<?php

namespace Rvdv\React\Tests\Nntp\Command;

use Rvdv\React\Nntp\Command\OverviewCommand;

class OverviewCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function commandExpectsMultilineResponse()
    {
        $command = new OverviewCommand($this->createStreamMock(), 10, array());

        $this->assertTrue($command->expectsMultilineResponse());
    }

    /**
     * @test
     */
    public function commandShouldNotReturnInitialResult()
    {
        $command = new OverviewCommand($this->createStreamMock(), 10, array());

        $this->assertNull($command->getResult());
    }

    private function createStreamMock()
    {
        return $this->getMockBuilder('React\Stream\Stream')
                    ->disableOriginalConstructor()
                    ->getMock();
    }
}
