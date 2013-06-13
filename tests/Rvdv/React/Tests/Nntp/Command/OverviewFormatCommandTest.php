<?php

namespace Rvdv\React\Tests\Nntp\Command;

use Rvdv\React\Nntp\Command\OverviewFormatCommand;

class OverviewFormatCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function commandExpectsMultilineResponse()
    {
        $command = new OverviewFormatCommand($this->createStreamMock());

        $this->assertTrue($command->expectsMultilineResponse());
    }

    /**
     * @test
     */
    public function commandShouldNotReturnInitialResult()
    {
        $command = new OverviewFormatCommand($this->createStreamMock());

        $this->assertNull($command->getResult());
    }

    private function createStreamMock()
    {
        return $this->getMockBuilder('React\Stream\Stream')
                    ->disableOriginalConstructor()
                    ->getMock();
    }
}
