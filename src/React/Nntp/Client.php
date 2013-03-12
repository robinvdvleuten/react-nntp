<?php

namespace React\Nntp;

use React\Dns\Resolver\Resolver;
use React\EventLoop\LoopInterface;
use React\Nntp\Command\CommandInterface;
use React\Nntp\Connection\Connection;
use React\Nntp\Response\ResponseInterface;
use React\Promise\When;

/**
 * Client
 *
 * @author Robin van der Vleuten <robinvdvleuten@gmail.com>
 */
class Client
{
    private $connection;
    private $loop;
    private $secureConnector;
    private $stream;

    /**
     * Constructor.
     *
     * @param \React\EventLoop\LoopInterface    $loop       A LoopInterface instance.
     * @param \React\Nntp\Connection\Connection $connection A Connection instance.
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
        return $this
            ->authInfo('user', $username)
            ->then(
                function (CommandInterface $command) use ($password) {
                    if (ResponseInterface::AUTHENTICATION_CONTINUE == $command->getResponse()->getStatusCode()) {
                        return $this->authInfo('pass', $password);
                    }

                    return When::resolve($command);
                },
                function (Exception $e) {
                    return When::reject($e);
                }
            )
            ->then(
                function (CommandInterface $command) {
                    if (ResponseInterface::AUTHENTICATION_ACCEPTED != $command->getResponse()->getStatusCode()) {
                        return When::reject(new \RuntimeException(sprintf(
                            "Could not authenticate with the provided username/password: [%d] %s",
                            $command->getResponse()->getStatusCode(),
                            $command->getResponse()->getMessage()
                        )));
                    }

                    return When::resolve($command);
                },
                function (\Exception $e) {
                    return When::reject($e);
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
        $this->connection->close();
        $this->loop->stop();
    }

    /**
     * @method \React\Promise\PromiseInterface authInfo(string $type, string $value)
     * @method \React\Promise\PromiseInterface group(string $name)
     * @method \React\Promise\PromiseInterface overview(string $range, array $format)
     * @method \React\Promise\PromiseInterface overviewFormat()
     */
    public function __call($command, $arguments)
    {
        return $this->connection->executeCommand($command, $arguments);
    }
}
