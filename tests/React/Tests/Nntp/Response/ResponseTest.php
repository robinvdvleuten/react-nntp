<?php

namespace React\Tests\Nntp\Response;

use React\Nntp\Response\Response;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function responseShouldBeCreatedFromString()
    {
        $response = Response::createFromString("200 Successful response");

        $this->assertInstanceOf('React\\Nntp\\Response\\ResponseInterface', $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Successful response", $response->getMessage());
    }

    /**
     * @test
     */
    public function invalidArgumentWhenInvalidString()
    {
        $this->setExpectedException('InvalidArgumentException');

        Response::createFromString("A very very invalid string");
    }

    /**
     * @test
     */
    public function runtimeErrorWhenInvalidStatusCode()
    {
        $this->setExpectedException('RuntimeException');

        $response = Response::createFromString("000 Unknown status code");
    }

    /**
     * @test
     */
    public function indicatingMultilineWhenSpecificStatusCode()
    {
        $response = Response::createFromString("222 Multiline response");

        $this->assertTrue($response->isMultilineResponse());
    }

    /**
     * @test
     */
    public function indicatingNotMultilineWhenSpecificStatusCode()
    {
        $response = Response::createFromString("200 Not multiline response");

        $this->assertFalse($response->isMultilineResponse());
    }
}
