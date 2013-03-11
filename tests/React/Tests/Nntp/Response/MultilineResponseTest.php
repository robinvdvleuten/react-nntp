<?php

namespace React\Tests\Nntp\Response;

use React\Nntp\Response\MultilineResponse;

class MultilineResponseTest extends \PHPUnit_Framework_TestCase
{
    private $loop;
    private $response;
    private $stream;

    public function setUp()
    {
        $this->response = $this->getMock('React\Nntp\Response\ResponseInterface');

        $this->loop = $this->getMock('React\EventLoop\LoopInterface');

        $this->stream = $this->getMockbuilder('React\Stream\Stream')
            ->disableOriginalConstructor()
            ->getMock();
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

        $multilineResponse = new MultilineResponse($this->response, $this->stream, $this->loop);

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
        $multilineResponse = new MultilineResponse($this->response, $this->stream, $this->loop);

        $multilineResponse->handleData(".\r\n");

        $lines = $multilineResponse->getLines();

        $this->assertTrue(is_array($lines));
        $this->assertTrue(empty($lines));
    }

    /**
     * @test
     */
    public function dataShouldBeExplodedToLines()
    {
        $multilineResponse = new MultilineResponse($this->response, $this->stream, $this->loop);

        $multilineResponse->handleData("Appended line\r\n");
        $multilineResponse->handleData("Appended line\r\n");
        $multilineResponse->handleData(".\r\n");

        $lines = $multilineResponse->getLines();

        $this->assertTrue(is_array($lines));
        $this->assertEquals(2, count($lines));
    }
}
