<?php

namespace React\Nntp;

use React\EventLoop\LoopInterface;
use React\SocketClient\ConnectorInterface;

class Connector implements ConnectorInterface
{
    private $connector;
    private $loop;

    public function __construct(ConnectorInterface $connector, LoopInterface $loop)
    {
        $this->connector = $connector;
        $this->loop = $loop;
    }

    public function createTcp($host, $port)
    {
        return $this->connector->createTcp($host, $port);
    }

    public function createUdp($host, $port)
    {
        return $this->connector->createUdp($host, $port);
    }

    public function handleConnectedSocket($socket)
    {
        return new Stream($socket, $this->loop);
    }
}
