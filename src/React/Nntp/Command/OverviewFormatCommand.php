<?php

namespace React\Nntp\Command;

use React\Nntp\Group;
use React\Nntp\Response\MultilineResponseInterface;
use React\Nntp\Response\ResponseInterface;

class OverviewFormatCommand extends AbstractCommand
{
    protected $format;

    /**
     * {@inheritDoc}
     */
    public function execute()
    {
        return 'LIST OVERVIEW.FMT';
    }

    /**
     * {@inheritDoc}
     */
    public function expectsMultilineResponse()
    {
        return true;
    }

    public function getFormat()
    {
        return $this->format;
    }

    /**
     * {@inheritDoc}
     */
    public function getResponseHandlers()
    {
        return array(
            ResponseInterface::GROUPS_FOLLOW => array(
                $this, 'handleResponse'
            ),
            ResponseInterface::NO_SUCH_GROUP => array(
                $this, 'handleErrorResponse'
            )
        );
    }

    public function handleResponse(MultilineResponseInterface $response)
    {
        $this->format = array();
        foreach ($response->getLines() as $line) {
            if (0 == strcasecmp(substr($line, -5, 5), ':full')) {
                // ':full' is _not_ included in tag, but value set to true
                $this->format[strtolower(substr($line, 0, -5))] = true;
            } else {
                // ':' is _not_ included in tag; value set to false
                $this->format[strtolower(substr($line, 0, -1))] = false;
            }
        }
    }

    public function handleErrorResponse(ResponseInterface $response)
    {
        var_dump($response);
    }
}
