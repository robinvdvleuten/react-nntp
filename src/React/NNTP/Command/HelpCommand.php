<?php

namespace React\NNTP\Command;

class HelpCommand implements CommandInterface
{
    public function __construct()
    {

    }

    /**
     * {@inheritDoc}
     */
    public function execute()
    {
        return 'HELP';
    }

    /**
     * {@inheritDoc}
     */
    public function expectsMultilineResponse()
    {
        return true;
    }
}
