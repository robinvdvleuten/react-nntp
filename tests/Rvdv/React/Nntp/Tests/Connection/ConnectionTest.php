<?php

namespace Rvdv\React\Nntp\Tests\Connection;

use Phake;
use React\EventLoop\StreamSelectLoop;
use React\Socket\ConnectionInterface;
use React\Socket\Server;
use Rvdv\React\Nntp\Connection\Connection;
use Rvdv\React\Nntp\Tests\TestCase;

class ConnectionTest extends TestCase
{
    /**
     * @test
     */
    public function factoryShouldReturnAConnection()
    {
        $loop = Phake::mock('React\EventLoop\StreamSelectLoop');
        $dns = Phake::mock('React\Dns\Resolver\Resolver');

        $connection = Connection::factory($loop, $dns);
        $this->assertInstanceOf('Rvdv\React\Nntp\Connection\Connection', $connection);
    }

    /**
     * @test
     */
    public function connectionToNntpServerShouldReturnResponse()
    {
        $receivedResponse = null;

        $loop = new StreamSelectLoop();
        $dns = Phake::mock('React\Dns\Resolver\Resolver');

        $server = new Server($loop);
        $server->on('connection', $this->expectCallableOnce());
        $server->on('connection', function (ConnectionInterface $connection) use ($server, $loop) {
            $connection->write("200 Hello from the NNTP server\r\n");
        });
        $server->listen(9999);

        $connection = Connection::factory($loop, $dns);
        $connection->connect('127.0.0.1', 9999)
            ->then(function ($response) use (&$receivedResponse, $loop) {
                $receivedResponse = $response;
                $loop->stop();
            })
        ;

        $loop->run();

        $this->assertInstanceOf('Rvdv\React\Nntp\Response\ResponseInterface', $receivedResponse);
        $this->assertEquals(200, $receivedResponse->getStatusCode());
        $this->assertEquals("Hello from the NNTP server", $receivedResponse->getMessage());
    }

    /**
     * @test
     */
    public function connectionToNntpServerShouldThrowExceptionWhenUnsuccessfull()
    {
        $receivedException = null;

        $loop = new StreamSelectLoop();
        $dns = Phake::mock('React\Dns\Resolver\Resolver');

        $server = new Server($loop);
        $server->on('connection', $this->expectCallableOnce());
        $server->on('connection', function (ConnectionInterface $connection) use ($server, $loop) {
            $connection->write("502 NNTP server is permanently unavailable\r\n");
        });
        $server->listen(9999);

        $connection = Connection::factory($loop, $dns);
        $connection->connect('127.0.0.1', 9999)
            ->then($this->expectCallableNever(), function (\Exception $e) use (&$receivedException, $loop) {
                $receivedException = $e;
                $loop->stop();
            })
        ;

        $loop->run();

        $this->assertInstanceOf('Rvdv\React\Nntp\Exception\BadResponseException', $receivedException);
        $this->assertEquals(502, $receivedException->getResponse()->getStatusCode());
        $this->assertEquals("NNTP server is permanently unavailable", $receivedException->getResponse()->getMessage());
    }


    /**
     * @test
     */
    public function connectionToUnknownNntpServerShouldFail()
    {
        $loop = new StreamSelectLoop();
        $dns = Phake::mock('React\Dns\Resolver\Resolver');

        $connection = Connection::factory($loop, $dns);
        $connection->connect('127.0.0.1', 9999)
            ->then($this->expectCallableNever(), $this->expectCallableOnce())
        ;

        $loop->run();
    }
}
