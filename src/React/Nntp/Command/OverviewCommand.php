<?php

namespace React\Nntp\Command;

use React\Nntp\Group;
use React\Nntp\Response\MultilineResponseInterface;
use React\Nntp\Response\ResponseInterface;

class OverviewCommand extends AbstractCommand
{
    protected $articles;
    protected $format;
    protected $range;

    public function __construct($range, $format)
    {
        $this->range = $range;

        // Prepend 'number' field
        $this->format = array_merge(array('number' => false), $format);
    }

    /**
     * {@inheritDoc}
     */
    public function execute()
    {
        return 'XOVER ' . $this->range;
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
                $this, 'handleResponse'
            ),
            ResponseInterface::NO_SUCH_GROUP => array(
                $this, 'handleErrorResponse'
            )
        );
    }

    public function handleResponse(MultilineResponseInterface $response)
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

    public function handleErrorResponse(ResponseInterface $response)
    {
        var_dump($response);
    }
}
