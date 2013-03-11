<?php

namespace React\Nntp\Command;

use React\EventLoop\LoopInterface;
use React\Nntp\Response\ResponseInterface;
use React\Stream\ReadableStreamInterface;
use React\Stream\Stream;

class AuthInfoCommand extends Command implements CommandInterface
{
    protected $type;
    protected $value;

    public function __construct(Stream $stream, LoopInterface $loop, $type, $value)
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
        return [
            ResponseInterface::AUTHENTICATION_ACCEPTED => [
                $this, 'handleAuthenticatedResponse'
            ],
            ResponseInterface::AUTHENTICATION_CONTINUE => [
                $this, 'handleAuthenticatedResponse'
            ],
            ResponseInterface::AUTHENTICATION_REJECTED => [
                $this, 'handleErrorResponse'
            ],
        ];
    }

    public function handleAuthenticatedResponse(ResponseInterface $response)
    {
        // We do nothing with the response here.
        return;
    }
}
