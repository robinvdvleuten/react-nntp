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
use Rvdv\React\Nntp\Response\ResponseInterface;
use Rvdv\React\Nntp\Command\QuitCommand;

/**
 * QuitCommandTest
 *
 * @author Robin van der Vleuten <robinvdvleuten@gmail.com>
 */
class QuitCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testCommandShouldWriteStreamWhenExecuting()
    {
        $stream = Phake::mock('React\Stream\Stream');

        $command = new QuitCommand($stream);
        $command->execute();

        Phake::verify($stream, Phake::times(1))->write("QUIT\r\n");
    }

    public function testCommandShouldHandleAllResponseCodes()
    {
        $stream = Phake::mock('React\Stream\Stream');

        $command = new QuitCommand($stream);

        $handlers = $command->getResponseHandlers();
        $this->assertCount(2, $handlers);

        foreach (array(
            ResponseInterface::CONNECTION_CLOSING,
            ResponseInterface::SYNTAX_ERROR_IN_COMMAND,
            ) as $handler) {
            $this->assertArrayHasKey($handler, $handlers);
            $this->assertTrue(is_callable($handlers[$handler]));
        }
    }

    public function testCommandNotExpectsMultilineResponse()
    {
        $stream = Phake::mock('React\Stream\Stream');

        $command = new QuitCommand($stream);

        $this->assertFalse($command->expectsMultilineResponse());
    }

    public function testCommandShouldNotReturnInitialResult()
    {
        $stream = Phake::mock('React\Stream\Stream');

        $command = new QuitCommand($stream);

        $this->assertNull($command->getResult());
    }

    public function testCommandShouldDoNothingWithResponse()
    {
        $stream = Phake::mock('React\Stream\Stream');

        $command = new QuitCommand($stream);

        $response = Phake::mock('Rvdv\React\Nntp\Response\MultilineResponseInterface');
        $command->handleConnectionClosingResponse($response);

        $this->assertNull($command->getResult());
    }
}
