<?php

/*
 * This file is part of React NNTP.
 *
 * (c) Robin van der Vleuten <robinvdvleuten@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rvdv\React\Nntp\Connection;

use React\EventLoop\LoopInterface;
use Rvdv\React\Nntp\Command\Command;
use Rvdv\React\Nntp\Command\CommandInterface;
use Rvdv\React\Nntp\Exception\BadResponseException;
use Rvdv\React\Nntp\Response\Response;
use Rvdv\React\Nntp\Response\ResponseInterface;
use React\Promise\Deferred;
use React\SocketClient\Connector;
use React\SocketClient\ConnectorInterface;
use React\SocketClient\SecureConnector;
use React\Stream\Stream;
use React\Stream\Util;
use RuntimeException;

/**
 * Connection
 *
 * @author Robin van der Vleuten <robinvdvleuten@gmail.com>
 */
class Connection
{
    private $connector;
    private $loop;
    private $secureConnector;
    private $stream;

    /**
     * Constructor.
     */
    public function __construct(ConnectorInterface $connector, ConnectorInterface $secureConnector, LoopInterface $loop)
    {
        $this->connector = $connector;
        $this->secureConnector = $secureConnector;
        $this->loop = $loop;
    }

    public static function factory(LoopInterface $loop, $resolver)
    {
        $connector = new Connector($loop, $resolver);
        $secureConnector = new SecureConnector($connector, $loop);

        return new static($connector, $secureConnector, $loop);
    }

    /**
     * Create a connection to the given NNTP server.
     *
     * @param string $address   The address of the server.
     * @param int    $port      The port of the server.
     * @param string $transport The transport method of the connection.
     */
    public function connect($address, $port, $transport = 'tcp')
    {
        return $this
            ->getConnectorForTransport($transport)
            ->create($address, $port)
            ->then([$this, 'handleConnect'])
        ;
    }

    /**
     * Close the current connected stream.
     */
    public function close()
    {
        // Stream is only available when successfully connected.
        if ($this->stream) {
            $this->stream->close();
        }
    }

    public function executeCommand($command, $arguments)
    {
        $class = sprintf('Rvdv\\React\\Nntp\\Command\\%sCommand', str_replace(" ", "", ucwords(strtr($command, "_-", "  "))));
        if (!class_exists($class) || !in_array('Rvdv\\React\\Nntp\\Command\\CommandInterface', class_implements($class))) {
            throw new RuntimeException(sprintf(
                "Given command '%s' is mapped to a non-existing class (%s).",
                $command,
                $class
            ));
        }

        $arguments = array_merge(array($this->stream), $arguments);

        $reflect  = new \ReflectionClass($class);

        $command = $reflect->newInstanceArgs($arguments);

        $deferred = new Deferred();

        $command->on('end', function (\Exception $error = null) use ($command, $deferred) {
            if ($error) {
                return $deferred->reject($error);
            }

            $deferred->resolve($command);
        });

        $command->execute();

        return $deferred->promise();
    }

    /**
     * Triggered when a connection is established with the NNTP server.
     *
     * @param \React\Stream\Stream $stream
     */
    public function handleConnect(Stream $stream)
    {
        $this->stream = $stream;
        // @todo make this configurable.
        $this->stream->bufferSize = 1024;

        $response = new Response();
        Util::pipe($this->stream, $response);

        $deferred = new Deferred();

        $response->on('close', function () use ($response, $deferred) {
            // Remove listeners on stream so we get no conflicting listeners on future response objects.
            $this->stream->removeAllListeners();

            if (!in_array($response->getStatusCode(), array(ResponseInterface::SERVICE_AVAILABLE_POSTING_ALLOWED, ResponseInterface::SERVICE_AVAILABLE_POSTING_PROHIBITED))) {
                return $deferred->reject(BadResponseException::factory($response));
            }

            $deferred->resolve($response);
        });

        return $deferred->promise();
    }

    protected function getConnectorForTransport($transport = 'tcp')
    {
        if (in_array($transport, ['ssl', 'tsl'])) {
            return $this->secureConnector;
        } else {
            return $this->connector;
        }
    }
}
