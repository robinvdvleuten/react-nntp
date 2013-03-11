<?php

namespace React\Nntp\Command;

use React\EventLoop\LoopInterface;
use React\Nntp\Response\ResponseInterface;
use React\Stream\ReadableStreamInterface;

class AuthInfoCommand extends Command implements CommandInterface
{
    protected $type;
    protected $value;

    public function __construct(ReadableStreamInterface $stream, LoopInterface $loop, $type, $value)
    {
        $this->type = $type;
        $this->value = $value;

        parent::__construct($stream, $loop);
    }

    /**
     * {@inheritDoc}
     */
    public function execute()
    {
        return $this->end("AUTHINFO " . $this->type . " " . $this->value . "\r\n");
    }

    /**
     * {@inheritDoc}
     */
    public function getResult()
    {
        return;
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
    public function getResponseHandlers()
    {
        return array(
            ResponseInterface::AUTHENTICATION_ACCEPTED => array(
                $this, 'handleAuthenticatedResponse'
            ),
            ResponseInterface::AUTHENTICATION_CONTINUE => array(
                $this, 'handleAuthenticatedResponse'
            ),
            ResponseInterface::AUTHENTICATION_REJECTED => array(
                $this, 'handleErrorResponse'
            )
        );
    }

    public function handleAuthenticatedResponse(ResponseInterface $response)
    {
        // We do nothing with the response here.
        return;
    }
}
