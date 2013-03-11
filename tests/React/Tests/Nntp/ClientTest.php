<?php

namespace React\Tests\Nntp;

use React\EventLoop\StreamSelectLoop;
use React\Nntp\Client;
use React\Socket\ConnectionInterface;
use React\Socket\Server;

class ClientTest extends TestCase
{
    /**
     * @test
     */
    public function factoryShouldReturnAClient()
    {
        $loop = $this->createLoopMock();
        $dns = $this->createResolverMock();

        $client = Client::factory($loop, $dns);
        $this->assertInstanceOf('React\\Nntp\\Client', $client);
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
        $server->on('connection', function ($connection) use ($server, $loop) {
            $connection->write("200 Hello from the NNTP server\r\n");
        });
        $server->listen(9999);

        $client = Client::factory($loop, $dns);
        $client->connect('127.0.0.1', 9999)
            ->then(function ($response) use (&$receivedResponse, $loop) {
                $receivedResponse = $response;
                $loop->stop();
            })
        ;

        $client->run();

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

        $client = Client::factory($loop, $dns);
        $client->connect('127.0.0.1', 9999)
            ->then($this->expectCallableNever(), $this->expectCallableOnce())
        ;

        $client->run();
    }

    /**
     * @test
     */
    public function runAndStopShouldCallLoopInstanceMethods()
    {
        $loop = $this->createLoopMock();
        $loop->expects($this->once())->method('run');
        $loop->expects($this->once())->method('stop');

        $dns = $this->createResolverMock();

        $client = Client::factory($loop, $dns);

        $client->run();
        $client->stop();
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
