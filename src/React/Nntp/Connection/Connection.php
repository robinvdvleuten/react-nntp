<?php

namespace React\Nntp\Connection;

use Exception;
use React\Dns\Resolver\Resolver;
use React\EventLoop\LoopInterface;
use React\Nntp\Command\CommandInterface;
use React\Nntp\Response\MultilineResponse;
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
    private $secureConnector;
    private $stream;

    /**
     * Constructor.
     */
    public function __construct(ConnectorInterface $connector, ConnectorInterface $secureConnector)
    {
        $this->connector = $connector;
        $this->secureConnector = $secureConnector;
    }

    public static function factory(LoopInterface $loop, $resolver)
    {
        $connector = new Connector($loop, $resolver);
        $secureConnector = new SecureConnector($connector, $loop);

        return new static($connector, $secureConnector);
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
            ->createTcp($address, $port)
            ->then(
                array($this, 'onConnect'),
                array($this, 'onError')
            );
    }

    public function executeCommand(CommandInterface $command)
    {
        $deferred = new Deferred();
        $stream = $this->stream;

        $stream->on('data', function ($data) use ($command, $deferred, $stream) {
            if (empty($data)) {
                return;
            }

            // Do not listen to incoming data events anymore.
            $stream->removeAllListeners('data');

            $response = Response::createFromString($data);
            $command->setResponse($response);

            $handlers = $command->getResponseHandlers();

            // Check if we received a response expected by the command.
            if (!isset($handlers[$response->getStatusCode()])) {
                throw new RuntimeException(sprintf(
                    "Unexpected response received: [%d] %s",
                    $response->getStatusCode(),
                    $response->getMessage()
                ));
            }

            if ($response->isMultilineResponse() && $command->expectsMultilineResponse()) {
                // Convert the response to a multiline response.
                $response = MultilineResponse::createFromResponse($response);
                $command->setResponse($response);

                if (!$response->isFinished()) {
                    return $stream->on('data', function ($data) use ($command, $response, $stream, $handlers, $deferred) {
                        $response->appendData($data);

                        if ($response->isFinished()) {
                            // Do not listen for data on the stream anymore.
                            $stream->removeAllListeners('data');

                            // Let the command's handler process the received multiline response.
                            call_user_func_array($handlers[$response->getStatusCode()], array($response));

                            // Resolve the multiline result of the command.
                            return $deferred->resolve($command);
                        }
                    });
                }
            }

            // Let the command's handler process the received response.
            call_user_func_array($handlers[$response->getStatusCode()], array($response));
            return $deferred->resolve($command);
        });

        $stream->write($command->execute() . "\r\n");
        return $deferred->promise();
    }

    /**
     * Triggered when a connection is established with the NNTP server.
     *
     * @param \React\Stream\Stream $stream
     */
    public function onConnect(Stream $stream)
    {
        $this->stream = $stream;

        $deferred = new Deferred();
        $stream->once('data', function ($data) use ($deferred) {
            $response = Response::createFromString($data);
            // @todo Check if it is a 200 response.

            return $deferred->resolve($response);
        });

        return $deferred->promise();
    }

    public function onError(Exception $e)
    {
        var_dump($e);
        return $e;
    }

    protected function getConnectorForTransport($transport = 'tcp')
    {
        if (in_array($transport, array('ssl', 'tsl'))) {
            return $this->secureConnector;
        } else {
            return $this->connector;
        }
    }
}
