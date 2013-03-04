<?php

namespace React\Nntp\Command;

use React\Nntp\Exception\BadResponseException;
use React\Nntp\Response\ResponseInterface;

/**
 * Abstract base class for Nntp commands.
 *
 * @author Robin van der Vleuten <robinvdvleuten@gmail.com>
 */
abstract class AbstractCommand implements CommandInterface
{
    protected $response;

    /**
     * {@inheritDoc}
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * {@inheritDoc}
     */
    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;
    }

    public function handleErrorResponse(ResponseInterface $response)
    {
        throw BadResponseException::factory($response);
    }
}
