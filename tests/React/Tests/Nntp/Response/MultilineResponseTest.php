<?php

namespace React\Tests\Nntp\Response;

use React\Nntp\Response\MultilineResponse;

class MultilineResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function multilineResponseShouldBeCreatedFromResponse()
    {
        $response = Response::createFromString("200 Successful response");
        $multilineResponse = MultilineResponse::createFromResponse($response);

        $this->assertInstanceOf('React\\Nntp\\Response\\MultilineResponseInterface', $multilineResponse);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Successful response", $response->getMessage());
    }
}
