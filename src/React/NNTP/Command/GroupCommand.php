<?php

namespace React\NNTP\Command;

use React\NNTP\Group;
use React\NNTP\ResponseInterface;

class GroupCommand implements CommandInterface
{
    protected $group;
    protected $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritDoc}
     */
    public function execute()
    {
        return 'GROUP ' . $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function expectsMultilineResponse()
    {
        return false;
    }

    public function getGroup()
    {
        return $this->group;
    }

    /**
     * {@inheritDoc}
     */
    public function getResponseHandlers()
    {
        return array(
            ResponseInterface::GROUP_SELECTED => array(
                $this, 'handleResponse'
            ),
            ResponseInterface::NO_SUCH_GROUP => array(
                $this, 'handleErrorResponse'
            )
        );
    }

    public function handleResponse(ResponseInterface $response)
    {
        $parts = explode(' ', $response->getMessage());
        $this->group = new Group($parts[3], $parts[0], $parts[1], $parts[2]);

        var_dump($this->group);
    }

    public function handleErrorResponse(ResponseInterface $response)
    {
        var_dump($response);
    }
}
