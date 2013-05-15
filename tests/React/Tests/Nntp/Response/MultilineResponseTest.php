<?php

namespace React\Tests\Nntp\Response;

use React\Nntp\Response\MultilineResponse;

class MultilineResponseTest extends \PHPUnit_Framework_TestCase
{
    private $response;

    public function setUp()
    {
        $this->response = $this->getMock('React\Nntp\Response\ResponseInterface');
    }

    /**
     * @test
     */
    public function multilineResponseShouldBeCreatedFromResponse()
    {
        $this->response->expects($this->once())
            ->method('getStatusCode')
            ->will($this->returnValue(200))
        ;

        $this->response->expects($this->once())
            ->method('getMessage')
            ->will($this->returnValue('Successful response'))
        ;

        $multilineResponse = new MultilineResponse($this->response);

        $this->assertInstanceOf('React\Nntp\Response\MultilineResponseInterface', $multilineResponse);
        $this->assertEquals(200, $multilineResponse->getStatusCode());
        $this->assertEquals('Successful response', $multilineResponse->getMessage());

        $this->assertTrue($multilineResponse->isMultilineResponse());

        $lines = $multilineResponse->getLines();

        $this->assertTrue(is_array($lines));
        $this->assertTrue(empty($lines));
    }

    /**
     * @test
     */
    public function responseIsFinishedWhenReceivedDot()
    {
        $multilineResponse = new MultilineResponse($this->response);

        $multilineResponse->write(".\r\n");

        $lines = $multilineResponse->getLines();

        $this->assertTrue(is_array($lines));
        $this->assertTrue(empty($lines));
    }

    /**
     * @test
     */
    public function dataShouldBeExplodedToLines()
    {
        $multilineResponse = new MultilineResponse($this->response);

        $multilineResponse->write("Appended line\r\n");
        $multilineResponse->write("Appended line\r\n");
        $multilineResponse->write(".\r\n");

        $lines = $multilineResponse->getLines();

        $this->assertTrue(is_array($lines));
        $this->assertEquals(2, count($lines));
    }
}
