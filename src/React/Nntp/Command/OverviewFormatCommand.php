<?php

namespace React\Nntp\Command;

use React\Nntp\Response\MultilineResponseInterface;
use React\Nntp\Response\ResponseInterface;

class OverviewFormatCommand extends Command implements CommandInterface
{
    private $format;

    /**
     * {@inheritDoc}
     */
    public function execute()
    {
        return $this->end("LIST OVERVIEW.FMT\r\n");
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
        return $this->format;
    }

    /**
     * {@inheritDoc}
     */
    public function getResponseHandlers()
    {
        return array(
            ResponseInterface::GROUPS_FOLLOW => array(
                $this, 'handleGroupsFollowResponse'
            ),
            ResponseInterface::NO_SUCH_GROUP => array(
                $this, 'handleErrorResponse'
            )
        );
    }

    public function handleGroupsFollowResponse(MultilineResponseInterface $response)
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
}
