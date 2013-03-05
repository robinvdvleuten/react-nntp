<?php

namespace React\Tests\Nntp\Command;

use React\Nntp\Command\OverviewCommand;

class OverviewCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function executeShouldReturnCorrectString()
    {
        $command = new OverviewCommand(10, array());

        $this->assertRegExp('/^XOVER 10$/', $command->execute());
    }

    /**
     * @test
     */
    public function commandExpectsMultilineResponse()
    {
        $command = new OverviewCommand(10, array());

        $this->assertTrue($command->expectsMultilineResponse());
    }
}
