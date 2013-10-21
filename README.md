# NNTP Component

Network News Transfer Protocol (NNTP) bindings for [React](http://reactphp.org).
This component builds on top of the `SocketClient` component to implement NNTP.

[![Latest Stable Version](https://poser.pugx.org/rvdv/react-nntp/v/stable.png)](https://packagist.org/packages/rvdv/react-nntp)
[![Total Downloads](https://poser.pugx.org/rvdv/react-nntp/downloads.png)](https://packagist.org/packages/rvdv/react-nntp)
[![Latest Unstable Version](https://poser.pugx.org/rvdv/react-nntp/v/unstable.png)](https://packagist.org/packages/rvdv/react-nntp)
[![Build Status](https://travis-ci.org/RobinvdVleuten/react-nntp.png?branch=master)](https://travis-ci.org/RobinvdVleuten/react-nntp) [![Coverage Status](https://coveralls.io/repos/RobinvdVleuten/react-nntp/badge.png?branch=master)](https://coveralls.io/r/RobinvdVleuten/react-nntp)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/51fcbf93-15c6-4d2d-9276-5688751754c3/mini.png)](https://insight.sensiolabs.com/projects/51fcbf93-15c6-4d2d-9276-5688751754c3)

## Install

The recommended way to install react-nntp is [through composer](http://getcomposer.org).

```JSON
{
    "require": {
        "rvdv/react-nntp": "1.0.*@dev"
    }
}
```

## Basic Usage

Here is a simple example that fetches the first 100 articles from the 'php.doc' newsgroup
of the PHP mailing list.

```php

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
        // Process the articles further.

        $client->stop();
    });

$client->run();
```

## Tests

To run the test suite, you need PHPUnit.

```bash
$ phpunit
```

## Vagrant

You can also use the configured [Vagrant](http://www.vagrantup.com) VM for local development.
Move into the `/vagrant` directory and run the following commands;

```bash
# Resolve the Puppet dependencies through librarian-puppet.
$ gem install librarian-puppet
$ librarian-puppet install

$ vagrant up
```
