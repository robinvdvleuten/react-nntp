<?php

namespace Rvdv\React\Nntp\Response;

/**
 * MultilineResponse
 *
 * @author Robin van der Vleuten <robinvdvleuten@gmail.com>
 */
class MultilineResponse extends Response implements MultilineResponseInterface
{
    private $lines = array();
    private $response;

    /**
     * Constructor
     *
     * @param \React\Nntp\Response\ResponseInterface $response A Response instance.
     */
    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
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

    public function write($data)
    {
        $this->buffer .= $data;

        if (false !== (bool) preg_match("/\r\n\.(\r\n)?$/", $this->buffer)) {
            $this->lines = explode("\r\n", trim($this->buffer));

            if (end($this->lines) === ".") {
                array_pop($this->lines);
            }

            $this->buffer = null;

            $this->close();
        }
    }
}
