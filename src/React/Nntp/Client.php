<?php

namespace React\Nntp;

use React\Dns\Resolver\Resolver;
use React\EventLoop\LoopInterface;
use React\Nntp\Command\AuthInfoCommand;
use React\Nntp\Command\CommandInterface;
use React\Nntp\Response\MultilineResponse;
use React\Nntp\Response\Response;
use React\Nntp\Response\ResponseInterface;
use React\Promise\Deferred;
use React\SocketClient\Connector;
use React\SocketClient\ConnectorInterface;
use React\SocketClient\SecureConnector;
use React\Stream\Stream;
use RuntimeException;

class Client
{
    public $stream;

    private $connector;
    private $loop;
    private $secureConnector;

    public static function factory(LoopInterface $loop, Resolver $resolver)
    {
        $connector = new Connector($loop, $resolver);
        $secureConnector = new SecureConnector($connector, $loop);

        return new static($loop, $connector, $secureConnector);
    }

    /**
     * Constructor.
     */
    public function __construct(LoopInterface $loop, ConnectorInterface $connector, ConnectorInterface $secureConnector)
    {
        $this->connector = $connector;
        $this->secureConnector = $secureConnector;
        $this->loop = $loop;
    }

    public function authenticate($username, $password)
    {
        $deferred = new Deferred();
        $that = $this;

        $command = new AuthInfoCommand('user', $username);
        return $this
            ->sendCommand($command)
            ->then(function (AuthInfoCommand $command) use ($password, $deferred, $that) {
                if (ResponseInterface::AUTHENTICATION_CONTINUE === $command->getResponse()->getStatusCode()) {
                    $command = new AuthInfoCommand('pass', $password);
                    return $that->sendCommand($command);
                }

                return $deferred->resolve($command);
            })
            ->then(function (AuthInfoCommand $command) {
                if (ResponseInterface::AUTHENTICATION_ACCEPTED !== $command->getResponse()->getStatusCode()) {
                    throw new RuntimeException(sprintf(
                        "Could not authenticate with the provided username/password: [%d] %s",
                        $response->getStatusCode(),
                        $response->getMessage()
                    ));
                }

                return $command;
            });
    }

    /**
     * Connect to the given NNTP server.
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
                array($this, 'handleConnection'),
                array($this, 'handleError')
            );
    }

    /**
     * Triggered when a connection is established with the NNTP server.
     *
     * @param \React\Stream\Stream $stream
     */
    public function handleConnection(Stream $stream)
    {
        $deferred = new Deferred();

        $this->stream = $stream;
        // $this->stream->pipe(new Stream(STDOUT, $this->loop));
        $this->stream->once('data', function ($data) use ($deferred) {
            $response = Response::createFromString($data);
            // @todo Check if it is a 200 response.

            return $deferred->resolve($response);
        });

        return $deferred->promise();
    }

    public function handleEnd()
    {
        var_dump(__FUNCTION__);
    }

    public function handleError(\Exception $e)
    {
        var_dump($e);
        return $e;
    }

    public function sendCommand(CommandInterface $command)
    {
        $deferred = new Deferred();
        $that = $this;

        $this->stream->on('data', function ($data) use ($command, $deferred, $that) {
            if (empty($data)) {
                return;
            }

            // Do not listen to incoming data events anymore.
            $that->stream->removeAllListeners('data');

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
                    return $that->stream->on('data', function ($data) use ($command, $response, $handlers) {
                        $lines = explode("\r\n", $data);
                        $response->appendLines($lines);

                        if ($response->isFinished()) {
                            // Do not listen for data on the stream anymore.
                            $that->stream->removeAllListeners('data');

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

        $this->stream->write($command->execute() . "\r\n");
        return $deferred->promise();
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
