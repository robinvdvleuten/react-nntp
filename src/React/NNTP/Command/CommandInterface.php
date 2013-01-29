<?php

namespace React\NNTP\Command;

use React\NNTP\Response;

/**
 * Interface for NNTP commands.
 *
 * @author Robin van der Vleuten <robinvdvleuten@gmail.com>
 */
interface CommandInterface
{
    public function execute();
    public function expectsMultilineResponse();
    public function getResponseHandlers();
}
