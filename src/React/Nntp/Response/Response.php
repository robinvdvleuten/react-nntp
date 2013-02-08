<?php

namespace React\Nntp\Response;

class Response implements ResponseInterface
{
    /**
     * @var integer
     */
    private $statusCode;

    /**
     * @var string
     */
    private $message;

    /**
     * Constructor
     *
     * @param integer $statusCode
     * @param string  $message
     */
    public function __construct($statusCode, $message)
    {
        $this->statusCode = (integer) $statusCode;
        $this->message    = (string) $message;
    }

    /**
     * Create a Response object from a string
     *
     * @return Nntp\Protocol\Response\Response
     *
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public static function createFromString($response)
    {
        if (!preg_match('/^(\d{3}) (.+)$/s', trim($response), $matches)) {
            throw new \InvalidArgumentException(
                sprintf('Invalid response: "%s"', trim($response))
            );
        }

        if ($matches[1] < 100 || $matches[1] >= 600) {
            throw new \RuntimeException(
                sprintf('Invalid status code: %d', $matches[1])
            );
        }

        return new static(
            $matches[1],
            $matches[2]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getStatusCode()
    {
        return $this->statusCode;
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
            array(
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
            )
        );
    }
}
