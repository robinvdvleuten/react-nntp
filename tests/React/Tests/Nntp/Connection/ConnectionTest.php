<?php

namespace React\Tests\Nntp\Connection;

use React\EventLoop\StreamSelectLoop;
use React\Nntp\Connection\Connection;
use React\Socket\ConnectionInterface;
use React\Socket\Server;
use React\Tests\Nntp\TestCase;

class ConnectionTest extends TestCase
{
    /**
     * @test
     */
    public function factoryShouldReturnAConnection()
    {
        $loop = $this->createLoopMock();
        $dns = $this->createResolverMock();

        $connection = Connection::factory($loop, $dns);
        $this->assertInstanceOf('React\\Nntp\\Connection\\Connection', $connection);
    }

    /**
     * @test
     */
    public function connectionToNntpServerShouldReturnResponse()
    {
        $receivedResponse = null;

        $loop = new StreamSelectLoop();
        $dns = $this->createResolverMock();

        $server = new Server($loop);
        $server->on('connection', $this->expectCallableOnce());
        $server->on('connection', function (ConnectionInterface $connection) use ($server, $loop) {
            $connection->write("200 Hello from the NNTP server");
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

        $this->assertInstanceOf('React\\Nntp\\Response\\ResponseInterface', $receivedResponse);
        $this->assertEquals(200, $receivedResponse->getStatusCode());
        $this->assertEquals("Hello from the NNTP server", $receivedResponse->getMessage());
    }

    /**
     * @test
     */
    public function connectionToUnknownNntpServerShouldFail()
    {
        $loop = new StreamSelectLoop();
        $dns = $this->createResolverMock();

        $connection = Connection::factory($loop, $dns);
        $connection->connect('127.0.0.1', 9999)
            ->then($this->expectCallableNever(), $this->expectCallableOnce())
        ;

        $loop->run();
    }

    private function createLoopMock()
    {
        return $this->getMockBuilder('React\EventLoop\StreamSelectLoop')
                    ->disableOriginalConstructor()
                    ->getMock();
    }

    private function createResolverMock()
    {
        return $this->getMockBuilder('React\Dns\Resolver\Resolver')
                    ->disableOriginalConstructor()
                    ->getMock();
    }
}
