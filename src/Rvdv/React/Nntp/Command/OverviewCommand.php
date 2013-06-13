<?php

namespace Rvdv\React\Nntp\Command;

use Rvdv\React\Nntp\Response\MultilineResponseInterface;
use Rvdv\React\Nntp\Response\ResponseInterface;
use React\Stream\Stream;

class OverviewCommand extends Command implements CommandInterface
{
    protected $articles;
    protected $format;
    protected $range;

    public function __construct(Stream $stream, $range, array $format)
    {
        $this->range = $range;

        // Prepend 'number' field
        $this->format = array_merge(['number' => false], $format);

        parent::__construct($stream);
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
        return [
            ResponseInterface::OVERVIEW_FOLLOWS => [
                $this, 'handleOverviewFollowsResponse'
            ],
            ResponseInterface::NO_SUCH_NEWSGROUP => [
                $this, 'handleErrorResponse'
            ],
            ResponseInterface::NO_ARTICLE_SELECTED => [
                $this, 'handleErrorResponse'
            ],
        ];
    }

    /**
     * Handler for a OVERVIEW_FOLLOWS response.
     *
     * @param \React\Nntp\Response\MultilineResponseInterface $response A MultilineResponseInterface instance.
     */
    public function handleOverviewFollowsResponse(MultilineResponseInterface $response)
    {
        $this->articles = [];

        foreach ($response->getLines() as $line) {
            $articleParts = explode("\t", $line);

            $field = 0;
            $article = [];
            foreach ($this->format as $name => $full) {
                $article[$name] = $full ? ltrim(substr($articleParts[$field], strpos($articleParts[$field], ':') + 1), " \t") : $articleParts[$field];
                $field++;
            }

            $this->articles[] = $article;
        }
    }
}
