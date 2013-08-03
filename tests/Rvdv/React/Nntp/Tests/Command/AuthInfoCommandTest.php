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
use Rvdv\React\Nntp\Command\AuthInfoCommand;

/**
 * AuthInfoCommandTest
 *
 * @author Robin van der Vleuten <robinvdvleuten@gmail.com>
 */
class AuthInfoCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function commandExpectsMultilineResponse()
    {
        $stream = Phake::mock('React\Stream\Stream');

        $command = new AuthInfoCommand($stream, 'type', 'value');

        $this->assertFalse($command->expectsMultilineResponse());
    }

    /**
     * @test
     */
    public function commandShouldNotReturnInitialResult()
    {
        $stream = Phake::mock('React\Stream\Stream');

        $command = new AuthInfoCommand($stream, 'type', 'value');

        $this->assertNull($command->getResult());
    }
}
