<?php

namespace React\Nntp\Response;

use Evenement\EventEmitter;
use React\Stream\ReadableStreamInterface;
use React\Stream\Util;
use React\Stream\WritableStreamInterface;

/**
 * Response
 *
 * @author Robin van der Vleuten <robinvdvleuten@gmail.com>
 */
class Response extends EventEmitter implements ResponseInterface, ReadableStreamInterface
{
    /**
     * @var string
     */
    private $buffer;

    /**
     * @var string
     */
    private $message;

    /**
     * @var boolean
     */
    private $readable = true;

    /**
     * @var integer
     */
    private $statusCode;

    /**
     * @var \React\Stream\WritableStreamInterface
     */
    private $stream;

    /**
     * Constructor
     *
     * @param \React\Stream\WritableStreamInterface $stream A WritableStreamInterface instance.
     */
    public function __construct(WritableStreamInterface $stream)
    {
        $this->stream = $stream;

        $this->stream->on('data', [$this, 'handleData']);
        $this->stream->on('end', [$this, 'handleEnd']);
        $this->stream->on('error', [$this, 'handleError']);
    }

    /**
     * {@inheritDoc}
     */
    public function getStatusCode()
    {
        return (int) $this->statusCode;
    }

    /**
     * {@inheritDoc}
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * {@inheritDoc}
     */
    public function isMultilineResponse()
    {
        return in_array(
            $this->getStatusCode(),
            [
                100, // HELP
                101, // CAPABILITIES
                211, // LISTGROUP (also not multi-line with GROUP)
                215, // LIST
                220, // ARTICLE
                221, // HEAD, XHDR
                222, // BODY
                224, // OVER, XOVER
                225, // HDR
                230, // NEWNEWS
                231, // NEWGROUPS
                282, // XGTITLE
            ]
        );
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

    public function close(\Exception $error = null)
    {
        if (!$this->readable) {
            return;
        }

        $this->readable = false;

        $this->emit('end', [$error, $this]);

        $this->removeAllListeners();
    }

    public function handleData($data)
    {
        $this->buffer .= $data;

        if (false !== strpos($this->buffer, "\r\n")) {
            if (!preg_match('/^(\d{3}) (.+)$/s', trim($this->buffer), $matches)) {
                throw new \InvalidArgumentException(
                    sprintf('Invalid response: "%s"', trim($this->buffer))
                );
            }

            if ($matches[1] < 100 || $matches[1] >= 600) {
                throw new \RuntimeException(
                    sprintf('Invalid status code: %d', $matches[1])
                );
            }

            $this->buffer = null;

            $this->stream->removeListener('data', [$this, 'handleData']);
            $this->stream->removeListener('end', [$this, 'handleEnd']);
            $this->stream->removeListener('error', [$this, 'handleError']);

            $this->statusCode = $matches[1];
            $this->message = $matches[2];

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
