<?php

namespace React\Nntp\Connection;

use React\EventLoop\LoopInterface;
use React\Nntp\Command\Command;
use React\Nntp\Command\CommandInterface;
use React\Nntp\Response\Response;
use React\Promise\Deferred;
use React\SocketClient\Connector;
use React\SocketClient\ConnectorInterface;
use React\SocketClient\SecureConnector;
use React\Stream\Stream;
use RuntimeException;

class Connection
{
    private $connector;
    private $loop;
    private $secureConnector;
    private $stream;

    /**
     * Constructor.
     */
    public function __construct(LoopInterface $loop, ConnectorInterface $connector, ConnectorInterface $secureConnector)
    {
        $this->loop = $loop;
        $this->connector = $connector;
        $this->secureConnector = $secureConnector;
    }

    public static function factory(LoopInterface $loop, $resolver)
    {
        $connector = new Connector($loop, $resolver);
        $secureConnector = new SecureConnector($connector, $loop);

        return new static($loop, $connector, $secureConnector);
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
        $class = sprintf('React\\Nntp\\Command\\%sCommand', str_replace(" ", "", ucwords(strtr($command, "_-", "  "))));
        if (!class_exists($class) || !in_array('React\\Nntp\\Command\\CommandInterface', class_implements($class))) {
            throw new RuntimeException(sprintf(
                "Given class %s is not a valid command.",
                $class
            ));
        }

        $arguments = array_merge([
            $this->stream,
            $this->loop,
        ], $arguments);

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

        $response = new Response($this->stream, $this->loop);
        $deferred = new Deferred();

        $response->on('end', function () use ($response, $deferred) {
            // @todo Check if it is a 200 response.
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
