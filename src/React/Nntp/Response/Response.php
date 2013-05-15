<?php

namespace React\Nntp\Response;

use React\Stream\WritableStream;

/**
 * Response
 *
 * @author Robin van der Vleuten <robinvdvleuten@gmail.com>
 */
class Response extends WritableStream implements ResponseInterface
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
     * @var integer
     */
    private $statusCode;

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
        return in_array($this->getStatusCode(), array(
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
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function write($data)
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

            list(, $this->statusCode, $this->message) = $matches;

            $this->close();
        }
    }

    public function __toString()
    {
        return $this->getStatusCode().' '.$this->getMessage();
    }
}
