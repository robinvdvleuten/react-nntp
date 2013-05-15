<?php

namespace React\Nntp\Response;

use Evenement\EventEmitter;
use React\Stream\ReadableStream;
use React\Stream\Util;
use React\Stream\WritableStreamInterface;

/**
 * MultilineResponse
 *
 * @author Robin van der Vleuten <robinvdvleuten@gmail.com>
 */
class MultilineResponse extends ReadableStream implements MultilineResponseInterface
{
    private $buffer;
    private $lines = array();
    private $response;
    private $stream;

    /**
     * Constructor
     *
     * @param \React\Nntp\Response\ResponseInterface $response A Response instance.
     * @param \React\Stream\WritableStreamInterface  $stream   A WritableStreamInterface instance.
     */
    public function __construct(ResponseInterface $response, WritableStreamInterface $stream)
    {
        $this->response = $response;
        $this->stream = $stream;

        $this->stream->on('data', array($this, 'handleData'));
        $this->stream->on('end', array($this, 'handleEnd'));
        $this->stream->on('error', array($this, 'handleError'));
    }

    /**
     * {@inheritDoc}
     */
    public function getStatusCode()
    {
        return $this->response->getStatusCode();
    }

    /**
     * {@inheritDoc}
     */
    public function getMessage()
    {
        return $this->response->getMessage();
    }

    /**
     * {@inheritDoc}
     */
    public function getLines()
    {
        return $this->lines;
    }

    /**
     * {@inheritDoc}
     */
    public function isMultilineResponse()
    {
        return true;
    }

    public function handleData($data)
    {
        $this->buffer .= $data;

        if (false !== (bool) preg_match("/\.\r\n$/", $this->buffer)) {
            $this->lines = explode("\r\n", trim($this->buffer));

            if (end($this->lines) === "") {
                array_pop($this->lines);
            }

            if (end($this->lines) === ".") {
                array_pop($this->lines);
            }

            $this->buffer = null;

            $this->stream->removeListener('data', array($this, 'handleData'));
            $this->stream->removeListener('end', array($this, 'handleEnd'));
            $this->stream->removeListener('error', array($this, 'handleError'));

            $this->close();
        }
    }

    public function handleEnd()
    {
        var_dump(__FUNCTION__);
    }

    public function handleError()
    {
        var_dump(__FUNCTION__);
    }
}
