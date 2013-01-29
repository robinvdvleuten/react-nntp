<?php

namespace React\Nntp;

use React\Dns\Resolver\Factory as DnsResolverFactory;
use React\EventLoop\Factory as EventLoopFactory;
use React\EventLoop\LoopInterface;
use React\Nntp\Command\AuthInfoCommand;
use React\Nntp\Command\CommandInterface;
use React\Promise\Deferred;
use React\SocketClient\Connector;
use React\SocketClient\ConnectorInterface;
use React\SocketClient\SecureConnector;
use React\Stream\Stream;
use RuntimeException;

class Client
{
    public $loop;
    public $stream;

    protected $connector;
    protected $secureConnector;

    public static function factory($dns = '8.8.8.8')
    {
        $loop = EventLoopFactory::create();

        $dnsResolverFactory = new DnsResolverFactory();
        $dns = $dnsResolverFactory->createCached('8.8.8.8', $loop);

        $connector = new Connector($loop, $dns);
        $secureConnector = new SecureConnector($connector, $loop);

        return new static($connector, $secureConnector, $loop);
    }

    /**
     * Constructor.
     */
    public function __construct(ConnectorInterface $connector, ConnectorInterface $secureConnector, LoopInterface $loop = null)
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

        $this->stream->once('data', function ($data) use ($command, $deferred, $that) {
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
                $messageParts = explode("\r\n", $response->getMessage());
                // Remove the first 'follows' message from the parts.
                array_shift($messageParts);
                // Create a buffer string from the message parts.
                $buffer = implode("\r\n", $messageParts);

                // Did we already reveived the multiline response?
                if (preg_match('/\.\r\n$/', $data, $matches)) {
                    // Let the command's handler process the received multiline response.
                    call_user_func_array($handlers[$command->getResponse()->getStatusCode()], array($command->getResponse(), $buffer));
                    // Resolve the multiline result of the command.
                    return $deferred->resolve($command);
                }

                return $that->stream->on('data', function ($data) use (&$buffer, $handlers, $command, $deferred, $that) {
                    // Append the received data to the buffer.
                    $buffer .= $data;

                    if (!preg_match('/\.\r\n$/', $data, $matches)) {
                        return;
                    }

                    // Remove the end line of the multiline response.
                    $buffer = preg_replace('/\r\n\.\r\n$/', '', $buffer);

                    // Do not listen for data on the stream anymore.
                    $that->stream->removeAllListeners('data');

                    // Let the command's handler process the received multiline response.
                    // @todo Do we need the check for existing handler again?
                    call_user_func_array($handlers[$command->getResponse()->getStatusCode()], array($command->getResponse(), $buffer));

                    // Resolve the multiline result of the command.
                    return $deferred->resolve($command);
                });
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
