<?php

namespace React\Nntp\Command;

use React\EventLoop\LoopInterface;
use React\Nntp\Group;
use React\Nntp\Response\MultilineResponseInterface;
use React\Nntp\Response\ResponseInterface;
use React\Stream\ReadableStreamInterface;

class OverviewCommand extends Command implements CommandInterface
{
    protected $articles;
    protected $format;
    protected $range;

    public function __construct(ReadableStreamInterface $stream, LoopInterface $loop, $range, array $format)
    {
        var_dump($range);
        $this->range = $range;

        // Prepend 'number' field
        $this->format = array_merge(array('number' => false), $format);

        parent::__construct($stream, $loop);
    }

    /**
     * {@inheritDoc}
     */
    public function execute()
    {
        return $this->end("XOVER " . $this->range . "\r\n");
    }

    /**
     * {@inheritDoc}
     */
    public function expectsMultilineResponse()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getResult()
    {
        return $this->articles;
    }

    /**
     * {@inheritDoc}
     */
    public function getResponseHandlers()
    {
        return array(
            ResponseInterface::OVERVIEW_FOLLOWS => array(
                $this, 'handleOverviewFollowsResponse'
            ),
            ResponseInterface::NO_SUCH_GROUP => array(
                $this, 'handleErrorResponse'
            ),
            ResponseInterface::NO_ARTICLE_SELECTED => array(
                $this, 'handleErrorResponse'
            ),
        );
    }

    /**
     * Handler for a OVERVIEW_FOLLOWS response.
     *
     * @param \React\Nntp\Response\MultilineResponseInterface $response A MultilineResponseInterface instance.
     */
    public function handleOverviewFollowsResponse(MultilineResponseInterface $response)
    {
        $this->articles = array();

        foreach ($response->getLines() as $line) {
            $articleParts = explode("\t", $line);

            $field = 0;
            $article = array();
            foreach ($this->format as $name => $full) {
                $article[$name] = $full ? ltrim(substr($articleParts[$field], strpos($articleParts[$field], ':') + 1), " \t") : $articleParts[$field];
                $field++;
            }

            $this->articles[] = $article;
        }
    }
}
