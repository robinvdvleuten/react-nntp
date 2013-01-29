<?php

namespace React\NNTP\Command;

/**
 * Base class for NNTP commands.
 *
 * @author Robin van der Vleuten <robinvdvleuten@gmail.com>
 */
class AbstractCommand implements CommandInterface
{
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
