<?php

require __DIR__.'/../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();

$dnsResolverFactory = new React\Dns\Resolver\Factory();
$dns = $dnsResolverFactory->createCached('8.8.8.8', $loop);

$client = React\Nntp\Client::factory($loop, $dns);

$group = null;
$format = null;

$client
    ->connect('news.php.net', 119)
    ->then(function (React\Nntp\Response\ResponseInterface $response) use ($client) {
        return $client->overviewFormat();
    })
    ->then(function (React\Nntp\Command\CommandInterface $command) use (&$format, $client) {
        $format = $command->getResult();

        return $client->group('php.doc');
    })
    ->then(function (React\Nntp\Command\CommandInterface $command) use (&$group, &$format, $client) {
        $group = $command->getResult();

        return $client->overview($group->getFirst() . '-' . ($group->getFirst() + 99), $format);
    })
    ->then(function (React\Nntp\Command\CommandInterface $command) use ($client) {
        $articles = $command->getResult();
        // Process the articles further.
        var_dump($articles);

        $client->stop();
    });

$client->run();
