<?php

namespace React\Nntp\Command;

use Evenement\EventEmitter;
use React\EventLoop\LoopInterface;
use React\Nntp\Exception\BadResponseException;
use React\Nntp\Response\MultilineResponse;
use React\Nntp\Response\Response;
use React\Nntp\Response\ResponseInterface;
use React\Stream\ReadableStreamInterface;
use React\Stream\Util;
use React\Stream\WritableStreamInterface;

abstract class Command extends EventEmitter implements CommandInterface, ReadableStreamInterface, WritableStreamInterface
{
    private $buffer;
    private $loop;
    private $readable = true;
    private $response;
    private $stream;
    private $writable = true;

    /**
     * Constructor.
     */
    public function __construct(ReadableStreamInterface $stream, LoopInterface $loop)
    {
        $this->stream = $stream;
        $this->loop = $loop;

        $this->response = new Response($this, $this->loop);

        $this->stream->on('drain', array($this, 'handleDrain'));
        $this->stream->on('data', array($this, 'handleData'));
        $this->stream->on('end', array($this, 'handleEnd'));
        $this->stream->on('error', array($this, 'handleError'));

        $this->on('response', array($this, 'handleResponse'));
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

        $this->emit('end', array($error, $this));

        $this->removeAllListeners();
    }

    public function handleDrain()
    {
        var_dump(__FUNCTION__);
    }

    public function handleData($data)
    {
        $this->buffer .= $data;
        $this->stream->pause();

        if (false !== strpos($this->buffer, "\r\n")) {
            $response = new Response($this, $this->loop);

            $buffer = $this->buffer;
            $this->buffer = null;

            $this->stream->removeListener('drain', array($this, 'handleDrain'));
            $this->stream->removeListener('data', array($this, 'handleData'));
            $this->stream->removeListener('end', array($this, 'handleEnd'));
            $this->stream->removeListener('error', array($this, 'handleError'));

            $this->setResponse($response);

            $that = $this;
            $stream = $this->stream;
            $loop = $this->loop;

            $response->on('end', function () use ($that, $stream, $loop, $response) {
                if ($response->isMultilineResponse() && $that->expectsMultilineResponse()) {
                    $response = new MultilineResponse($response, $stream, $loop);
                    $that->setResponse($response);

                    $response->on('end', function () use ($that, $response) {
                        $that->emit('response', array($response));
                        $that->close();
                    });

                    $stream->resume();
                } else {
                    $that->emit('response', array($response));
                    $that->close();
                }
            });

            $this->emit('data', array($buffer));
        }

        $this->stream->resume();
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

        call_user_func_array($handlers[$response->getStatusCode()], array($response));
    }

    public function handleErrorResponse(ResponseInterface $response)
    {
        throw BadResponseException::factory($response);
    }
}
