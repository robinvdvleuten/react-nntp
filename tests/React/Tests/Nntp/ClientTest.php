<?php

namespace React\Tests\Nntp;

use React\Nntp\Client;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function factoryShouldReturnAClientInstance()
    {
        $client = Client::factory();
        $this->assertInstanceOf('React\\Nntp\\Client', $client);
    }
}
