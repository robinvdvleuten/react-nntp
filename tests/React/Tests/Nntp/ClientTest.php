<?php

namespace React\Tests\Nntp;

use React\EventLoop\StreamSelectLoop;
use React\Nntp\Client;

class ClientTest extends TestCase
{
    public function testFactoryShouldReturnAClient()
    {
        $loop = $this->createLoopMock();
        $dns = $this->createResolverMock();

        $client = Client::factory($loop, $dns);
        $this->assertInstanceOf('React\\Nntp\\Client', $client);
    }

    public function testConnectShouldReturnPromise()
    {
        $loop = new StreamSelectLoop();
        $dns = $this->createResolverMock();

        $client = Client::factory($loop, $dns);
        $client->connect('127.0.0.1', 119)->then($this->expectCallableNever(), $this->expectCallableOnce());

        $client->run();
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
