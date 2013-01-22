<?php
namespace React\NNTP\Stream;

use React\NNTP\Client;
use React\Stream\WritableStream;

class InputStream extends WritableStream
{
    protected $buffer = '';
    protected $client;

    public function __construct()
    {
        // $that = $this;

        /* $client->on('end', function () use ($that) {
            $that->end();
        }); */
    }

    public function write($data)
    {
        $this->buffer .= $data;

        if (false !== strpos($this->buffer, "\n")) {
            $responses = explode("\n", $this->buffer);
            $tail = array_pop($responses);

            foreach ($responses as $response) {
                // $this->client->parseResponse($response);
            }

            $this->buffer = $tail;
        }
    }

    public function close()
    {
        if ($this->closed) {
            return;
        }

        parent::close();

        // $this->client->end();
    }
}
