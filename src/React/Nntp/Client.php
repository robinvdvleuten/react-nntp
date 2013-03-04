<?php

namespace React\Nntp;

use Exception;
use React\Dns\Resolver\Resolver;
use React\EventLoop\LoopInterface;
use React\Nntp\Command\AuthInfoCommand;
use React\Nntp\Command\CommandInterface;
use React\Nntp\Connection\Connection;
use React\Nntp\Response\MultilineResponse;
use React\Nntp\Response\Response;
use React\Nntp\Response\ResponseInterface;
use React\Promise\Deferred;
use React\SocketClient\Connector;
use React\SocketClient\ConnectorInterface;
use React\SocketClient\SecureConnector;
use React\Stream\Stream;
use ReflectionClass;
use RuntimeException;

class Client
{
    private $connector;
    private $loop;
    private $secureConnector;
    private $stream;

    /**
     * Constructor.
     */
    public function __construct(LoopInterface $loop, Connection $connection)
    {
        $this->loop = $loop;
        $this->connection = $connection;
    }

    public static function factory(LoopInterface $loop, Resolver $resolver)
    {
        $connection = Connection::factory($loop, $resolver);

        return new static($loop, $connection);
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
        return $this->connection->connect($address, $port, $transport);
    }

    public function authenticate($username, $password)
    {
        $deferred = new Deferred();
        $connection = $this->connection;

        $command = new AuthInfoCommand('user', $username);
        return $connection
            ->executeCommand($command)
            ->then(
                function (AuthInfoCommand $command) use ($password, $deferred, $connection) {
                    if (ResponseInterface::AUTHENTICATION_CONTINUE === $command->getResponse()->getStatusCode()) {
                        $command = new AuthInfoCommand('pass', $password);
                        return $connection->executeCommand($command);
                    }

                    return $deferred->resolve($command);
                },
                function (Exception $e) use ($deferred) {
                    return $deferred->reject($e);
                }
            )
            ->then(
                function (AuthInfoCommand $command) use ($deferred) {
                    if (ResponseInterface::AUTHENTICATION_ACCEPTED !== $command->getResponse()->getStatusCode()) {
                        return $deferred->reject(new RuntimeException(sprintf(
                            "Could not authenticate with the provided username/password: [%d] %s",
                            $response->getStatusCode(),
                            $response->getMessage()
                        )));
                    }

                    return $command;
                },
                function (Exception $e) use ($deferred) {
                    return $deferred->reject($e);
                }
            )
        ;
    }

    public function run()
    {
        $this->loop->run();
    }

    public function stop()
    {
        $this->loop->stop();
    }

    public function __call($method, $arguments)
    {
        $class = sprintf('React\\Nntp\\Command\\%sCommand', str_replace(" ", "", ucwords(strtr($method, "_-", "  "))));
        if (!class_exists($class) || !in_array('React\\Nntp\\Command\\CommandInterface', class_implements($class))) {
            throw new RuntimeException(sprintf(
                "Given class %s is not a valid command.",
                $class
            ));
        }

        $reflect  = new ReflectionClass($class);
        $command = $reflect->newInstanceArgs($arguments);

        return $this->connection->executeCommand($command);
    }
}
