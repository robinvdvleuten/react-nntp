<?php

namespace React\NNTP;

use Evenement\EventEmitter;
use React\EventLoop\Factory;
use React\NNTP\Stream\Stream;
use React\Promise\Deferred;
use React\Socket\Connection;

class Client extends EventEmitter
{
    protected $host;
    protected $loop;
    protected $port;

    public function __construct($host, $port)
    {
        $this->host = $host;
        $this->port = $port;

        $this->loop = Factory::create();
    }

    public function authenticate($user, $pass = null)
    {
        $deferred = new Deferred();
        $that = $this;

        $this->once('data', function ($status, $message) use ($pass, $that, $deferred) {
            if (381 === $status && null !== $pass) {
                $that->once('data', function($status, $message) use ($that, $deferred) {
                    $that->handleAuthentication($status, $message, $deferred);
                });

                return $that->emit('send_command', array('AUTHINFO pass ' . $pass));
            }

            $that->handleAuthentication($status, $message, $deferred);
        });

        $this->emit('send_command', array('AUTHINFO user ' . $user));
        return $deferred->promise();
    }

    public function close()
    {
        $this->emit('close');
    }

    public function connect()
    {
        $url = sprintf('tcp://%s:%d', $this->host, $this->port);

        if (!($socket = @stream_socket_client($url, $errno, $errstr, ini_get("default_socket_timeout"), STREAM_CLIENT_CONNECT | STREAM_CLIENT_ASYNC_CONNECT))) {
            throw new \RuntimeException(sprintf("Connection to %s failed: %s", $url, $errstr), $errno);
        }

        stream_set_blocking($socket, 0);
        $connection = new Connection($socket, $this->loop);

        $stream = new Stream($this);
        $connection->pipe($stream)->pipe($connection);

        $that = $this;
        $this->once('data', function ($status, $message) use ($that) {
            $that->emit('connected', func_get_args());
        });

        $this->loop->run();
    }

    public function handleAuthentication($status, $message, Deferred $deferred)
    {
        // @todo throw an error on an unsuccessfull response.
        $deferred->resolve(array($status, $message));
    }

    public function parseResponse($response)
    {
        if (!preg_match('/^(\d{3}) (.+)$/', trim($response), $matches)) {
            throw new \InvalidArgumentException(
                sprintf('Invalid response: "%s"', trim($response))
            );
        }

        if ($matches[1] < 100 || $matches[1] >= 600) {
            throw new \RuntimeException(
                sprintf('Invalid status code: %d', $matches[1])
            );
        }

        $this->emit('data', array((int) $matches[1], $matches[2]));
    }

    public function selectGroup($group)
    {
        $deferred = new Deferred();

        $this->once('data', function ($status, $message) use ($deferred) {
            // @todo throw an error on an unsuccessfull response.
            $messages = explode(' ', trim($message));
            $deferred->resolve(new Group($messages[3], $messages[0], $messages[1], $messages[2]));
        });

        $this->emit('send_command', array('GROUP ' . $group));
        return $deferred->promise();
    }
}
