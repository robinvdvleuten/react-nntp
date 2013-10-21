<?php

require __DIR__.'/../vendor/autoload.php';

use React\Dns\Resolver\Factory as ResolverFactory;
use React\EventLoop\Factory as EventLoopFactory;
use Rvdv\React\Nntp\Client;
use Rvdv\React\Nntp\Command\CommandInterface;
use Rvdv\React\Nntp\Response\ResponseInterface;

$loop = EventLoopFactory::create();

$dnsResolverFactory = new ResolverFactory();
$dns = $dnsResolverFactory->createCached('8.8.8.8', $loop);

$client = Client::factory($loop, $dns);

$group = null;
$format = null;

$client
    ->connect('news.php.net', 119)
    ->then(function (ResponseInterface $response) use ($client) {
        return $client->overviewFormat();
    })
    ->then(function (CommandInterface $command) use (&$format, $client) {
        $format = $command->getResult();

        return $client->group('php.doc');
    })
    ->then(function (CommandInterface $command) use (&$group, &$format, $client) {
        $group = $command->getResult();

        return $client->overview($group->getFirst() . '-' . ($group->getFirst() + 99), $format);
    })
    ->then(function (CommandInterface $command) use ($client) {
        $articles = $command->getResult();

        // Process the articles further
        // ...

        $client->stop();
    });

$client->run();
