<?php

namespace React\NNTP\Stream;

use React\NNTP\Client;
use React\Stream\CompositeStream;

class Stream extends CompositeStream
{
    public function __construct(Client $client)
    {
        $input = new InputStream($client);
        $output = new OutputStream($client);

        $that = $this;

        parent::__construct($output, $input);
    }
}
