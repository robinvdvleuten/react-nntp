<?php
namespace React\NNTP\Stream;

use React\NNTP\Client;
use React\Stream\ReadableStream;

class OutputStream extends ReadableStream
{
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;

        $that = $this;

        $client->on('close', function () use ($that) {
            $that->emit('close');
        });

        $client->on('send_command', function ($command) use ($that) {
            $that->emit('data', array($command . "\r\n"));
        });
    }
}
