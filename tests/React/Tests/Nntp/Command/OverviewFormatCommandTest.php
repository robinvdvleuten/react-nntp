<?php

namespace React\Tests\Nntp\Command;

use React\Nntp\Command\OverviewFormatCommand;

class OverviewFormatCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function executeShouldReturnCorrectString()
    {
        $command = new OverviewFormatCommand();

        $this->assertRegExp('/^LIST OVERVIEW.FMT$/', $command->execute());
    }

    /**
     * @test
     */
    public function commandExpectsMultilineResponse()
    {
        $command = new OverviewFormatCommand();

        $this->assertTrue($command->expectsMultilineResponse());
    }
}
