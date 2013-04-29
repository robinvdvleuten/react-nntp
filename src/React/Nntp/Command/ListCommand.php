<?php

namespace React\Nntp\Command;

use React\Nntp\Group;
use React\Nntp\Response\MultilineResponseInterface;
use React\Nntp\Response\ResponseInterface;

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
