<?php

namespace React\Nntp\Response;

class MultilineResponse extends Response implements MultilineResponseInterface
{
    private $finished = false;

    private $lines = array();

    /**
     * Constructor
     *
     * @param integer $statusCode
     * @param string  $message
     */
    public function __construct($statusCode, $message, array $lines)
    {
        parent::__construct($statusCode, $message);

        $this->appendLines($lines);
    }

    public static function createFromResponse(ResponseInterface $response)
    {
        // Explode the received message to lines.
        $lines = explode("\r\n", $response->getMessage());

        // Shift the first line, this is the main message.
        $message = array_shift($lines);

        return new static($response->getStatusCode(), $message, $lines);
    }

    /**
     * {@inheritDoc}
     */
    public function appendLines(array $lines)
    {
        $this->lines += $lines;

        // Check if we have finished receiving lines.
        if ($this->finished = "." === end($this->lines)) {
            // We do not need this dot in the multiline response.
            array_pop($this->lines);
        }
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
    public function isFinished()
    {
        return $this->finished;
    }

    /**
     * {@inheritDoc}
     */
    public function isMultilineResponse()
    {
        return true;
    }
}
