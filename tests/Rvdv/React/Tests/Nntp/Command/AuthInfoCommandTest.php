<?php

namespace Rvdv\React\Tests\Nntp\Command;

use Rvdv\React\Nntp\Command\AuthInfoCommand;

class AuthInfoCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function commandExpectsMultilineResponse()
    {
        $command = new AuthInfoCommand($this->createStreamMock(), 'type', 'value');

        $this->assertFalse($command->expectsMultilineResponse());
    }

    /**
     * @test
     */
    public function commandShouldNotReturnInitialResult()
    {
        $command = new AuthInfoCommand($this->createStreamMock(), 'type', 'value');

        $this->assertNull($command->getResult());
    }

    private function createStreamMock()
    {
        return $this->getMockBuilder('React\Stream\Stream')
                    ->disableOriginalConstructor()
                    ->getMock();
    }
}
