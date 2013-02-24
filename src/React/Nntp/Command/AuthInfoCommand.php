<?php

namespace React\Nntp\Command;

use React\Nntp\Group;
use React\Nntp\Response\ResponseInterface;

class AuthInfoCommand extends AbstractCommand
{
    protected $type;
    protected $value;

    public function __construct($type, $value)
    {
        $this->type = $type;
        $this->value = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function execute()
    {
        return 'AUTHINFO ' . $this->type . ' ' . $this->value;
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
                $this, 'handleResponse'
            ),
            ResponseInterface::AUTHENTICATION_CONTINUE => array(
                $this, 'handleResponse'
            ),
            ResponseInterface::AUTHENTICATION_REJECTED => array(
                $this, 'handleErrorResponse'
            )
        );
    }

    public function handleResponse(ResponseInterface $response)
    {
        // We do nothing with the response here.
        return;
    }

    public function handleErrorResponse(ResponseInterface $response)
    {
        var_dump($response);
    }
}
