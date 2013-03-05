<?php

namespace React\Tests\Nntp\Response;

use React\Nntp\Response\MultilineResponse;
use React\Nntp\Response\Response;

class MultilineResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function multilineResponseShouldBeCreatedFromResponse()
    {
        // @todo This should be a mock
        $response = Response::createFromString("200 Successful response");
        $multilineResponse = MultilineResponse::createFromResponse($response);

        $this->assertInstanceOf('React\\Nntp\\Response\\MultilineResponseInterface', $multilineResponse);
        $this->assertEquals(200, $multilineResponse->getStatusCode());
        $this->assertEquals("Successful response", $multilineResponse->getMessage());

        $this->assertTrue($multilineResponse->isMultilineResponse());
        $this->assertFalse($multilineResponse->isFinished());

        $lines = $multilineResponse->getLines();
        $this->assertTrue(is_array($lines));
        $this->assertTrue(empty($lines));
    }

    /**
     * @test
     */
    public function responseIsFinishedWhenReceivedDot()
    {
        // @todo This should be a mock
        $response = Response::createFromString("200 Successful response");
        $multilineResponse = MultilineResponse::createFromResponse($response);

        $multilineResponse->appendData("\r\n.\r\n");

        $this->assertTrue($multilineResponse->isFinished());

        $lines = $multilineResponse->getLines();
        $this->assertTrue(is_array($lines));
        $this->assertTrue(empty($lines));
    }

    /**
     * @test
     */
    public function dataShouldBeExplodedToLines()
    {
        // @todo This should be a mock
        $response = Response::createFromString("200 Successful response");
        $multilineResponse = MultilineResponse::createFromResponse($response);

        $multilineResponse->appendData("\r\nAppended line");
        $multilineResponse->appendData("\r\nAppended line");
        $multilineResponse->appendData("\r\n.\r\n");

        $this->assertTrue($multilineResponse->isFinished());

        $lines = $multilineResponse->getLines();
        $this->assertTrue(is_array($lines));
        $this->assertEquals(2, count($lines));
    }
}
