<?php

/*
 * This file is part of React NNTP.
 *
 * (c) Robin van der Vleuten <robinvdvleuten@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rvdv\React\Nntp\Command;

use Rvdv\React\Nntp\Response\MultilineResponseInterface;
use Rvdv\React\Nntp\Response\ResponseInterface;

/**
 * OverviewFormatCommand
 *
 * @author Robin van der Vleuten <robinvdvleuten@gmail.com>
 */
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
        return [
            ResponseInterface::GROUPS_FOLLOW => [
                $this, 'handleGroupsFollowResponse'
            ],
            ResponseInterface::NO_SUCH_NEWSGROUP => [
                $this, 'handleErrorResponse'
            ]
        ];
    }

    public function handleGroupsFollowResponse(MultilineResponseInterface $response)
    {
        $this->format = [];

        foreach ($response->getLines() as $line) {
            if (0 == strcasecmp(substr($line, -5, 5), ':full')) {
                // ':full' is _not_ included in tag, but value set to true
                $this->format[str_replace('-', '_', strtolower(substr($line, 0, -5)))] = true;
            } else {
                // ':' is _not_ included in tag; value set to false
                $this->format[str_replace('-', '_', strtolower(substr($line, 0, -1)))] = false;
            }
        }
    }
}
