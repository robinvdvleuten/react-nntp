<?php

namespace React\Nntp\Command;

use React\Nntp\Response\ResponseInterface;

/**
 * Interface for Nntp commands.
 *
 * @author Robin van der Vleuten <robinvdvleuten@gmail.com>
 */
interface CommandInterface
{
    public function getResponse();
    public function setResponse(ResponseInterface $response);

    public function execute();
    public function expectsMultilineResponse();
    public function getResponseHandlers();
    public function getResult();
}
