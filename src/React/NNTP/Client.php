<?php

namespace React\NNTP;

use Evenement\EventEmitter;
use React\Curry;
use React\Dns\Resolver\Factory as DnsResolverFactory;
use React\EventLoop\Factory as EventLoopFactory;
use React\EventLoop\LoopInterface;
use React\NNTP\Command\CommandInterface;
use React\Promise\Deferred;
use React\SocketClient\ConnectionManager;
use React\SocketClient\ConnectionManagerInterface;
use React\SocketClient\SecureConnectionManager;
use React\Stream\BufferedSink;
use React\Stream\CompositeStream;
use React\Stream\Stream;
use React\Stream\Util;

class Client extends EventEmitter
{
    public $input;

    protected $connectionManager;
    protected $loop;
    protected $secureConnectionManager;
    protected $stream;

    public static function factory($dns = '8.8.8.8')
    {
        $loop = EventLoopFactory::create();

        $dnsResolverFactory = new DnsResolverFactory();
        $dns = $dnsResolverFactory->createCached('8.8.8.8', $loop);

        $connectionManager = new ConnectionManager($loop, $dns);
        $secureConnectionManager = new SecureConnectionManager($connectionManager, $loop);

        return new static($connectionManager, $secureConnectionManager, $loop);
    }

    /**
     * Constructor.
     */
    public function __construct(ConnectionManagerInterface $connectionManager, ConnectionManagerInterface $secureConnectionManager, LoopInterface $loop = null)
    {
        $this->connectionManager = $connectionManager;
        $this->secureConnectionManager = $secureConnectionManager;
        $this->loop = $loop;
    }

    public function authenticate()
    {

    }

    public function bufferMultilineResponse(Response $response, CommandInterface $command, Deferred $deferred, &$buffer, $data)
    {
        // Append the received data to the buffer.
        $buffer .= $data;

        // Check if we received the end of the multiline response.
        if (!preg_match('/\.\r\n$/', $data, $matches)) {
            return;
        }

        // Remove the end line of the multiline response.
        $buffer = preg_replace('/\r\n\.\r\n$/', '', $buffer);

        // Do not listen for data on the stream anymore.
        $this->stream->removeAllListeners('data');

        // Let the command's handler process the received multiline response.
        // @todo Do we need the check for existing handler again?
        $handlers = $command->getResponseHandlers();
        call_user_func_array($handlers[$response->getStatusCode()], array($response, $buffer, $deferred));

        return $deferred->resolve($command);
    }

    /**
     * Connect to the given NNTP server.
     *
     * @param string $address The address of the server.
     * @param int    $port    The port of the server.
     */
    public function connect($address, $port)
    {
        $this->connectionManager
            ->getConnection($address, $port)
            ->then(Curry\Bind(array($this, 'handleConnect')))
        ;

        $this->loop->run();
    }

    /**
     * Triggered when a connection is established with the NNTP server.
     *
     * @param \React\Stream\Stream $stream
     */
    public function handleConnect(Stream $stream)
    {
        $this->stream = $stream;
        // $this->stream->pipe(new Stream(STDOUT, $this->loop));

        // Attach listeners to stream events.
        $this->stream->on('end', array($this, 'handleEnd'));
        $this->stream->on('error', array($this, 'handleError'));

        $that = $this;
        // Listen to incoming data once, which means we have connected to the NNTP server.
        $this->stream->once('data', function ($data) use ($that) {
            $response = Response::createFromString($data);
            // Tell listeners that we've established a connection.
            // @todo Check if it is a 200 response.
            $that->emit('connection', array($response));
        });
    }

    public function handleEnd()
    {
        var_dump(__FUNCTION__);
    }

    public function handleError(\Exception $e)
    {
        throw $e;
    }

    public function sendCommand(CommandInterface $command)
    {
        $deferred = new Deferred();

        $that = $this;
        $stream = $this->stream;
        $this->stream->once('data', function ($data) use ($that, $stream, $command, $deferred) {
            $handlers = $command->getResponseHandlers();
            $response = Response::createFromString($data);

            // Check if we received a response expected by the command.
            if (!isset($handlers[$response->getStatusCode()])) {
                var_dump("UNKNOWN HANDLER FOR " . $response->getStatusCode());
                // @todo this seems not to be working.
                /* return $deferred->reject(new \Exception(sprintf(
                    'Unexpected response received: "%d %s"',
                    $response->getStatusCode(),
                    $response->getMessage()
                ))); */
            }

            // It's a multiline response, so process it further.
            if ($response->isMultilineResponse() && $command->expectsMultilineResponse()) {
                $buffer = "";
                return $stream->on('data', Curry\bind(array($that, 'bufferMultilineResponse'), $response, $command, $deferred, $buffer));
            }

            // Let the command's handler process the received response.
            call_user_func_array($handlers[$response->getStatusCode()], array($response));
            $deferred->resolve($command);
        });

        $this->stream->write($command->execute() . "\r\n");
        return $deferred->promise();
    }
}
