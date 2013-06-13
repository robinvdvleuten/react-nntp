<?php

namespace Rvdv\React\Nntp\Exception;

use Rvdv\React\Nntp\Response\ResponseInterface;

class BadResponseException extends \Exception
{
    private $response;

    public static function factory(ResponseInterface $response)
    {
        $message = 'Unsuccessful response' . PHP_EOL . implode(PHP_EOL, [
            '[status code] ' . $response->getStatusCode(),
            '[message] ' . $response->getMessage(),
        ]);

        $exception = new static($message);
        $exception->setResponse($response);

        return $exception;
    }

    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;
    }

    public function getResponse()
    {
        return $this->response;
    }
}
