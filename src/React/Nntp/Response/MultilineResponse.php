<?php

namespace React\Nntp\Response;

class MultilineResponse extends Response implements MultilineResponseInterface
{
    private $finished = false;

    private $data = "";
    private $lines;

    /**
     * Constructor
     *
     * @param integer $statusCode
     * @param string  $message
     */
    public function __construct($statusCode, $message, $data)
    {
        parent::__construct($statusCode, $message);

        $this->appendData($data);
    }

    public static function createFromResponse(ResponseInterface $response)
    {
        // Explode the received message to lines.
        $lines = explode("\r\n", $response->getMessage());

        // Shift the first line, this is the main message.
        $message = array_shift($lines);

        // Sometimes we've received parts of lines, so put the lines back as string.
        $data = implode("\r\n", $lines);

        return new static($response->getStatusCode(), $message, $data);
    }

    /**
     * {@inheritDoc}
     */
    public function appendData($data)
    {
        $this->data .= $data;

        // Check if we have finished receiving lines.
        if ($this->finished = preg_match("/\r\n.(\r\n)?$/", $this->data)) {
            $this->lines = explode("\r\n", trim($this->data));

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
