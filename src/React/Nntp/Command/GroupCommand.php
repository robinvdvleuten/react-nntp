<?php

namespace React\Nntp\Command;

use React\EventLoop\LoopInterface;
use React\Nntp\Group;
use React\Nntp\Response\ResponseInterface;
use React\Stream\ReadableStreamInterface;

class GroupCommand extends Command implements CommandInterface
{
    protected $group;
    protected $name;

    /**
     * Constructor.
     */
    public function __construct(ReadableStreamInterface $stream, LoopInterface $loop, $name)
    {
        $this->name = $name;

        parent::__construct($stream, $loop);
    }

    /**
     * {@inheritDoc}
     */
    public function execute()
    {
        return $this->end("GROUP " . $this->name . "\r\n");
    }

    /**
     * {@inheritDoc}
     */
    public function expectsMultilineResponse()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getResult()
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
                $this, 'handleGroupSelectedResponse'
            ),
            ResponseInterface::NO_SUCH_GROUP => array(
                $this, 'handleErrorResponse'
            ),
        );
    }

    public function handleGroupSelectedResponse(ResponseInterface $response)
    {
        $parts = explode(' ', $response->getMessage());
        $this->group = new Group($parts[3], $parts[0], $parts[1], $parts[2]);
    }
}
