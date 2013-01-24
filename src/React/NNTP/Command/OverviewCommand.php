<?php

namespace React\NNTP\Command;

use React\NNTP\Group;
use React\NNTP\ResponseInterface;

class OverviewCommand implements CommandInterface
{
    protected $articles;
    protected $format;
    protected $range;

    public function __construct($range, $format)
    {
        $this->range = $range;

        // Prepend 'Number' field
        $this->format = array_merge(array('Number' => false), $format);
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

    public function getArticles()
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
            /* ResponseInterface::NO_SUCH_GROUP => array(
                $this, 'handleErrorResponse'
            ) */
        );
    }

    public function handleResponse(ResponseInterface $response, $buffer)
    {
        $this->articles = array();

        $articleStrings = explode("\r\n", trim($buffer));
        foreach ($articleStrings as $articleString) {
            $articleParts = explode("\t", $articleString);

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
