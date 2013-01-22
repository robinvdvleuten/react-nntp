<?php

namespace React\NNTP;

use Evenement\EventEmitter;
use React\EventLoop\Factory;
use React\NNTP\Stream\InputStream;
use React\NNTP\Stream\OutputStream;
use React\Promise\Deferred;
use React\Socket\Connection;

class Client extends EventEmitter
{
    const RESPONSECODE_READY_POSTING_ALLOWED = 200;
    const RESPONSECODE_READY_POSTING_PROHIBITED = 201;
    const RESPONSECODE_GROUP_SELECTED = 211;
    const RESPONSECODE_OVERVIEW_FOLLOWS = 224;
    const RESPONSECODE_NO_SUCH_GROUP = 411;
    const RESPONSECODE_AUTHENTICATION_REQUIRED = 480;
    const RESPONSECODE_NOT_PERMITTED = 502;

    public $input;

    protected $host;
    protected $loop;
    protected $port;

    public static function factory($address, $port)
    {
        $loop = Factory::create();
        $socket = stream_socket_client('tcp://$address:$port');
    }

    public function __construct($host, $port)
    {
        $this->host = $host;
        $this->port = $port;

        $this->loop = Factory::create();
    }

    public function connect()
    {
        $connection = $this->createConnection();

        $this->input = new InputStream();
        $this->input->on('error', array($this, 'handleErrorEvent'));
        $connection->pipe($this->input);

        $this->output = new OutputStream();
        $this->output->pipe($connection);

        $that = $this;
        $connection->on('error', function (\Exception $e) use ($that) {
            $that->input->emit('error', array($e));
        });

        $this->loop->run();
    }

    public function handleErrorEvent(\Exception $e)
    {
        $this->emit('error', array($e));
    }

    protected function createConnection()
    {
        $address = 'tcp://' . $this->host . ':' . $this->port;

        if (false === $socket = @stream_socket_client($address, $errno, $errstr)) {
            throw new ConnectionException("Could not bind to $address: $errstr", $errno);
        }

        return new Connection($socket, $this->loop);
    }

    /* public function authenticate($user, $pass = null)
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

    /**
     * Fetch an overview of article(s) in the currently selected group.
     */
    /*public function getOverview($range = null, $names = true, $forceNames = true)
    {
        $deferred = new Deferred();

        $this->once('data', function ($status, $message) use ($deferred) {
            if (Client::RESPONSECODE_OVERVIEW_FOLLOWS !== $status) {
                // return $deferred->reject(new \RuntimeException());
            }
        });

        if (is_null($range)) {
            $command = 'XOVER';
        } else {
            $command = 'XOVER ' . $range;
        }

        $this->emit('send_command', array($command));
        return $deferred;
    }

    public function handleAuthentication($status, $message, Deferred $deferred)
    {
        // @todo throw an error on an unsuccessfull response.
        if (Client::RESPONSECODE_NOT_PERMITTED === $status) {
            return $deferred->reject(new \RuntimeException(sprintf('Error when authentication with NNTP server (%s)', $message)));
        }

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

        return $this->emit('data', array((int) $matches[1], $matches[2]));
    }

    public function selectGroup($group)
    {
        $that = $this;
        $this->once('data', function ($status, $message) use ($group, $that) {
            if (Client::RESPONSECODE_GROUP_SELECTED !== $status) {
                throw new \RuntimeException(sprintf('Error when selecting group %s', $group));
            }

            $messages = explode(' ', trim($message));
            $that->emit('group_selected', array(new Group($messages[3], $messages[0], $messages[1], $messages[2])));
        });

        return $this->emit('send_command', array('GROUP ' . $group));
    } */
}
