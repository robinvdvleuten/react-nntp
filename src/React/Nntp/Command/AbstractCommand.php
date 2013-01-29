<?php

namespace React\Nntp\Command;

use React\Nntp\ResponseInterface;

/**
 * Abstract base class for Nntp commands.
 *
 * @author Robin van der Vleuten <robinvdvleuten@gmail.com>
 */
abstract class AbstractCommand implements CommandInterface
{
    protected $response;

    /**
     * {@inheritdoc}
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * {@inheritdoc}
     */
    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function execute();

    /**
     * {@inheritdoc}
     */
    abstract public function expectsMultilineResponse();

    /**
     * {@inheritdoc}
     */
    abstract public function getResponseHandlers();
}
