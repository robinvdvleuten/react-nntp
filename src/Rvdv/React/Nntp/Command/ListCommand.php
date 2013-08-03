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

use Rvdv\React\Nntp\Group;
use Rvdv\React\Nntp\Response\MultilineResponseInterface;
use Rvdv\React\Nntp\Response\ResponseInterface;

/**
 * ListCommand
 *
 * @author Robin van der Vleuten <robinvdvleuten@gmail.com>
 */
class ListCommand extends Command implements CommandInterface
{
    private $groups;

    /**
     * {@inheritDoc}
     */
    public function execute()
    {
        return $this->end("LIST ACTIVE\r\n");
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
        return $this->groups;
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
            ResponseInterface::SYNTAX_ERROR => [
                $this, 'handleErrorResponse',
            ],
            ResponseInterface::NOT_SUPPORTED => [
                $this, 'handleErrorResponse',
            ]
        ];
    }

    public function handleGroupsFollowResponse(MultilineResponseInterface $response)
    {
        $this->groups = [];

        foreach ($response->getLines() as $line) {
            $parts = explode(' ', $line);
            $this->groups[] = new Group($parts[0], 0, $parts[2], $parts[1], $parts[3] === 'y' ? true : false);
        }
    }
}
