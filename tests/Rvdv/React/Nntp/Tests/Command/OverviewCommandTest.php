<?php

/*
 * This file is part of React NNTP.
 *
 * (c) Robin van der Vleuten <robinvdvleuten@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rvdv\React\Nntp\Tests\Command;

use Phake;
use Rvdv\React\Nntp\Command\OverviewCommand;

/**
 * OverviewCommandTest
 *
 * @author Robin van der Vleuten <robinvdvleuten@gmail.com>
 */
class OverviewCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testCommandShouldNotReturnInitialResult()
    {
        $stream = Phake::mock('React\Stream\Stream');

        $command = new OverviewCommand($stream, 10, array());
        $this->assertNull($command->getResult());
    }

    public function testCommandCanHandleMultilineResponse()
    {
        $stream = Phake::mock('React\Stream\Stream');

        $command = new OverviewCommand($stream, 10, array(
            'subject' => false,
            'from' => false,
            'date' => false,
            'message_id' => false,
            'references' => false,
            'bytes' => false,
            'lines' => false,
            'xref' => true,
        ));

        $this->assertTrue($command->expectsMultilineResponse());

        $response = Phake::mock('Rvdv\React\Nntp\Response\MultilineResponseInterface');
        Phake::when($response)->getLines()->thenReturn(array(
            "123456789\tRe: Are you checking out React NNTP?\trobinvdvleuten@gmail.com (\"Robin van der Vleuten\")\tSat,3 Aug 2013 13:19:22 -0000\t<reactnntp123456789@nntp>\t<reactnntp987654321@nntp>\t321\t123\tXref: react.nntp:123456789",
        ));

        $command->handleOverviewFollowsResponse($response);

        $articles = $command->getResult();
        $this->assertCount(1, $articles);

        $article = reset($articles);
        $this->assertInstanceOf('Rvdv\React\Nntp\Article', $article);

        $this->assertEquals('123456789', $article->getNumber());
        $this->assertEquals('Re: Are you checking out React NNTP?', $article->getSubject());
        $this->assertEquals('robinvdvleuten@gmail.com ("Robin van der Vleuten")', $article->getFrom());
        $this->assertEquals('Sat,3 Aug 2013 13:19:22 -0000', $article->getDate());
        $this->assertEquals('<reactnntp123456789@nntp>', $article->getMessageId());
        $this->assertEquals('<reactnntp987654321@nntp>', $article->getReferences());
        $this->assertEquals('321', $article->getBytes());
        $this->assertEquals('123', $article->getLines());
        $this->assertEquals('react.nntp:123456789', $article->getXref());
    }
}
