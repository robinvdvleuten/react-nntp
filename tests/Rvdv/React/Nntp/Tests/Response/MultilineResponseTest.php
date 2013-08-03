<?php

/*
 * This file is part of React NNTP.
 *
 * (c) Robin van der Vleuten <robinvdvleuten@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rvdv\React\Nntp\Tests\Response;

use Phake;
use Rvdv\React\Nntp\Response\MultilineResponse;

/**
 * MultilineResponseTest
 *
 * @author Robin van der Vleuten <robinvdvleuten@gmail.com>
 */
class MultilineResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function multilineResponseShouldBeCreatedFromResponse()
    {
        $response = Phake::mock('Rvdv\React\Nntp\Response\ResponseInterface');

        Phake::when($response)->getStatusCode()->thenReturn(200);
        Phake::when($response)->getMessage()->thenReturn('Successful response');

        $multilineResponse = new MultilineResponse($response);

        $this->assertInstanceOf('Rvdv\React\Nntp\Response\MultilineResponseInterface', $multilineResponse);
        $this->assertEquals(200, $multilineResponse->getStatusCode());
        $this->assertEquals('Successful response', $multilineResponse->getMessage());

        $this->assertTrue($multilineResponse->isMultilineResponse());

        $lines = $multilineResponse->getLines();

        $this->assertTrue(is_array($lines));
        $this->assertTrue(empty($lines));

        Phake::verify($response, Phake::times(1))->getStatusCode();
        Phake::verify($response, Phake::times(1))->getMessage();
    }

    /**
     * @test
     */
    public function responseIsFinishedWhenReceivedDot()
    {
        $response = Phake::mock('Rvdv\React\Nntp\Response\ResponseInterface');

        $multilineResponse = new MultilineResponse($response);

        $multilineResponse->write(".");

        $lines = $multilineResponse->getLines();

        $this->assertTrue(is_array($lines));
        $this->assertTrue(empty($lines));
    }

    /**
     * @test
     */
    public function dataShouldBeExplodedToLines()
    {
        $response = Phake::mock('Rvdv\React\Nntp\Response\ResponseInterface');

        $multilineResponse = new MultilineResponse($response);

        $multilineResponse->write("Appended line\r\n");
        $multilineResponse->write("Appended line\r\n");
        $multilineResponse->write(".");

        $lines = $multilineResponse->getLines();

        $this->assertTrue(is_array($lines));
        $this->assertEquals(2, count($lines));
    }
}
