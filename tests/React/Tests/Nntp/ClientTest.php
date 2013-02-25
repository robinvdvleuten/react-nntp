<?php

namespace React\Tests\Nntp;

use React\Nntp\Client;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryShouldReturnAClient()
    {
        $loop = $this->getMock('React\EventLoop\LoopInterface', array(), array(), '', false);
        $resolver = $this->getMock('React\Dns\Resolver\Resolver', array(), array(), '', false);

        $client = Client::factory($loop, $resolver);
        $this->assertInstanceOf('React\\Nntp\\Client', $client);
    }
}
