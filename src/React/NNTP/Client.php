<?php

namespace React\NNTP;

use Evenement\EventEmitter;
use React\Dns\Resolver\Factory as DnsResolverFactory;
use React\EventLoop\Factory as EventLoopFactory;
use React\EventLoop\LoopInterface;
use React\NNTP\Stream\InputStream;
use React\NNTP\Stream\OutputStream;
use React\SocketClient\ConnectionManager;
use React\SocketClient\ConnectionManagerInterface;
use React\SocketClient\SecureConnectionManager;
use React\Stream\CompositeStream;
use React\Stream\Stream;

class Client extends EventEmitter
{
    public $input;

    protected $buffer;
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

    public function bufferStream($data)
    {
        $this->buffer .= $data;

        if (preg_match('/\.\r\n$/', $data, $matches)) {
            $this->stream->removeListener('data', array($this, 'bufferStream'));
            var_dump($this->buffer);
        }
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
            ->then(array($this, 'handleConnect'))
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

    public function sendCommand($command)
    {
        $this->buffer = "";
        $this->stream->on('data', array($this, 'bufferStream'));

        $this->stream->write($command . "\r\n");
    }
}
