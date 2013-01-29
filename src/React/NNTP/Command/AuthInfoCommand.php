<?php

namespace React\Nntp\Command;

use React\Nntp\Group;
use React\Nntp\ResponseInterface;

class AuthInfoCommand extends AbstractCommand
{
    protected $password;
    protected $username;

    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * {@inheritDoc}
     */
    public function execute()
    {
        return 'AUTHINFO user ' . $this->username;
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

    public function handleResponse(ResponseInterface $response, $buffer)
    {
        var_dump($response);
    }

    public function handleErrorResponse(ResponseInterface $response)
    {
        var_dump($response);
    }
}
