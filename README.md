# NNTP Component

Network News Transfer Protocol (NNTP) bindings for React.
This component builds on top of the `Socket` component to implement NNTP.

[![Build Status](https://travis-ci.org/RobinvdVleuten/reactphp-nntp.png?branch=master)](https://travis-ci.org/RobinvdVleuten/reactphp-nntp)

## Install

The recommended way to install reactphp-nntp is [through composer](http://getcomposer.org).

```JSON
{
    "require": {
        "rvdv/reactphp-nntp": "1.0.*"
    }
}
```

## Basic Usage

Here is a simple example that fetches the first 100 articles from the 'php.doc' newsgroup
of the PHP mailing list.

```php
use React\Nntp\Client;
use React\Nntp\Command\GroupCommand;
use React\Nntp\Command\OverviewCommand;
use React\Nntp\Command\OverviewFormatCommand;

$client = Client::factory();

$group = null;
$format = null;

$client
    ->connect('news.php.net', 119)
    ->then(function ($response) use ($client) {
        $command = new OverviewFormatCommand();
        return $client->sendCommand($command);
    })
    ->then(function (OverviewFormatCommand $command) use (&$format, $client) {
        $format = $command->getFormat();

        $command = new GroupCommand('php.doc');
        return $client->sendCommand($command);
    })
    ->then(function (GroupCommand $command) use (&$group, &$format, $client) {
        $group = $command->getGroup();

        $command = new OverviewCommand($group->getFirst() . '-' . ($group->getFirst() + 99), $format);
        return $client->sendCommand($command);
    })
    ->then(function (OverviewCommand $command) use ($client) {
        $articles = $command->getArticles();
        // Process the articles further.

        $client->loop->stop();
    });

$client->loop->run();
```

## Tests

To run the test suite, you need PHPUnit.

    $ phpunit
