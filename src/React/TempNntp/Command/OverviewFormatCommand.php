<?php

namespace React\Nntp\Command;

use React\Nntp\Group;
use React\Nntp\ResponseInterface;

class OverviewFormatCommand implements CommandInterface
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
            /* ResponseInterface::NO_SUCH_GROUP => array(
                $this, 'handleErrorResponse'
            ) */
        );
    }

    public function handleResponse(ResponseInterface $response, $buffer)
    {
        $this->format = array();
        foreach (explode("\r\n", $buffer) as $part) {
            if (0 == strcasecmp(substr($part, -5, 5), ':full')) {
                // ':full' is _not_ included in tag, but value set to true
                $this->format[substr($part, 0, -5)] = true;
            } else {
                // ':' is _not_ included in tag; value set to false
                $this->format[substr($part, 0, -1)] = false;
            }
        }
    }

    public function handleErrorResponse(ResponseInterface $response)
    {
        var_dump($response);
    }
}
