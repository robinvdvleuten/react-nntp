<?php

namespace React\Nntp\Command;

use Evenement\EventEmitter;
use React\Nntp\Exception\BadResponseException;
use React\Nntp\Response\MultilineResponse;
use React\Nntp\Response\Response;
use React\Nntp\Response\ResponseInterface;
use React\Stream\ReadableStreamInterface;
use React\Stream\Stream;
use React\Stream\Util;
use React\Stream\WritableStreamInterface;

abstract class Command extends EventEmitter implements CommandInterface, ReadableStreamInterface, WritableStreamInterface
{
    private $buffer;
    private $readable = true;
    private $response;
    private $stream;
    private $writable = true;

    /**
     * Constructor.
     */
    public function __construct(Stream $stream)
    {
        $this->stream = $stream;

        $this->stream->on('drain', [$this, 'handleDrain']);
        $this->stream->on('data', [$this, 'handleData']);
        $this->stream->on('end', [$this, 'handleEnd']);
        $this->stream->on('error', [$this, 'handleError']);

        $this->on('response', [$this, 'handleResponse']);
    }

    /**
     * {@inheritDoc}
     */
    abstract public function execute();

    /**
     * {@inheritDoc}
     */
    abstract public function expectsMultilineResponse();

    /**
     * {@inheritDoc}
     */
    abstract public function getResponseHandlers();

    /**
     * {@inheritDoc}
     */
    abstract public function getResult();

    /**
     * {@inheritDoc}
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * {@inheritDoc}
     */
    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;
    }

    public function isReadable()
    {
        return $this->readable;
    }

    public function pause()
    {

    }

    public function resume()
    {

    }

    public function pipe(WritableStreamInterface $dest, array $options = array())
    {
        Util::pipe($this, $dest, $options);

        return $dest;
    }

    public function isWritable()
    {
        return $this->writable;
    }

    public function write($data)
    {
        if (!$this->isWritable()) {
            return;
        }

        return $this->stream->write($data);
    }

    public function end($data = null)
    {
        if (null !== $data && !is_scalar($data)) {
            throw new \InvalidArgumentException('$data must be null or scalar');
        }

        if (null !== $data) {
            $this->write($data);
        }
    }

    public function close(\Exception $error = null)
    {
        if (!$this->readable) {
            return;
        }

        $this->readable = false;

        $this->emit('end', [$error, $this]);

        $this->removeAllListeners();
    }

    public function handleDrain()
    {
        var_dump(__CLASS__);
        var_dump(__FUNCTION__);
    }

    public function handleData($data)
    {
        $this->buffer .= $data;

        if (false !== strpos($this->buffer, "\r\n")) {
            $response = new Response();
            $this->pipe($response);

            $buffer = $this->buffer;
            $this->buffer = null;

            $this->setResponse($response);

            $response->on('end', function () use ($response) {
                $this->stream->removeAllListeners();

                if ($response->isMultilineResponse() && $this->expectsMultilineResponse()) {
                    $multilineResponse = new MultilineResponse($response);
                    Util::pipe($this->stream, $multilineResponse);

                    $this->setResponse($multilineResponse);

                    $multilineResponse->on('end', function () use ($multilineResponse) {
                        $this->emit('response', [$multilineResponse]);
                        $this->close();
                    });

                    $this->stream->resume();
                } else {
                    $this->emit('response', [$response]);
                    $this->close();
                }
            });

            $this->emit('data', [$buffer]);
        }
    }

    public function handleEnd()
    {
        var_dump(__CLASS__);
        var_dump(__FUNCTION__);
    }

    public function handleError()
    {
        var_dump(__CLASS__);
        var_dump(__FUNCTION__);
    }

    public function handleResponse(ResponseInterface $response)
    {
        $handlers = $this->getResponseHandlers();

        // Check if we received a response expected by the command.
        if (!isset($handlers[$response->getStatusCode()])) {
            return $this->close(new \RuntimeException(sprintf(
                "Unexpected response received: [%d] %s",
                $response->getStatusCode(),
                $response->getMessage()
            )));
        }

        call_user_func_array($handlers[$response->getStatusCode()], [$response]);
    }

    public function handleErrorResponse(ResponseInterface $response)
    {
        throw BadResponseException::factory($response);
    }
}
