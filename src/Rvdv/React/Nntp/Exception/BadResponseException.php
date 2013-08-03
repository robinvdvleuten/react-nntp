<?php

/*
 * This file is part of React NNTP.
 *
 * (c) Robin van der Vleuten <robinvdvleuten@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rvdv\React\Nntp\Exception;

use Rvdv\React\Nntp\Response\ResponseInterface;

/**
 * BadResponseException
 *
 * @author Robin van der Vleuten <robinvdvleuten@gmail.com>
 */
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
