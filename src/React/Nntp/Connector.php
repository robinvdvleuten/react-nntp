<?php

namespace React\Nntp;

use React\SocketClient\Connector as BaseConnector;

class Connector extends BaseConnector
{
    public function handleConnectedSocket($socket)
    {
        return new Stream($socket, $this->loop);
    }
}
