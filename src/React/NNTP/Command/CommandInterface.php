<?php

namespace React\NNTP\Command;

use React\NNTP\Response;

interface CommandInterface
{
    public function execute();
    public function expectsMultilineResponse();
    public function getResponseHandlers();
}
