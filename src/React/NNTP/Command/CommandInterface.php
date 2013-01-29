<?php

namespace React\Nntp\Command;

use React\Nntp\Response;

/**
 * Interface for Nntp commands.
 *
 * @author Robin van der Vleuten <robinvdvleuten@gmail.com>
 */
interface CommandInterface
{
    public function execute();
    public function expectsMultilineResponse();
    public function getResponseHandlers();
}
