# DNS Plugin

[Phergie](http://github.com/phergie/phergie-irc-bot-react/) plugin for Looking up IP&#039;s by hostnames.

[![Build Status](https://secure.travis-ci.org/phergie/phergie/phergie-irc-plugin-dns.png?branch=master)](http://travis-ci.org/phergie/phergie-irc-plugin-dns)

## Install

To install via [Composer](http://getcomposer.org/), use the command below, it will automatically detect the latest version and bind it with `~`.

```
composer require phergie/phergie-irc-plugin-dns 
```

See Phergie documentation for more information on
[installing and enabling plugins](https://github.com/phergie/phergie-irc-bot-react/wiki/Usage#plugins).

## Configuration

```php
new \Phergie\Plugin\Dns\Plugin([

    // All configuration is optional

    'dnsServer' => '1.2.3.4', // IP address of a DNS server, defaults to Google's 8.8.8.8

    // or

    'command' => 'customDns', // command name, defaults to dns

    // or

    'resolver' => new React\Dns\Resolver\Resolver(), // provide your own Resolver instance
                                                     // defaults to null and is set at first use
                                                     // (don't use this unless you know what you are doing!)

    // or

    'enableCommand' => false,  // enable use access to the dns command
])
```

## Events

This plugin listens on a few events providing the resolver to other plugins that wish to make use of it.

### dns.resolve

The `dns.resolve` event accepts a callback that will be called with a `Promise` that will resolve once the given hostname has been resolved. (If promises are new to you, be sure to read [this](https://gist.github.com/domenic/3889970).)

```php
$this->emitter->emit('dns.resolve', [function($promise) use ($callback, $that) {
    $promise->then(function($ip) {
        echo 'IP for github.com: ' . $ip . PHP_EOL;
    });
}]);
```

### dns.resolver

The `dns.resolver` event accepts a callback that will be called once a `Resolver` instance has been created.

```php
$this->emitter->emit('dns.resolver', [function($resolver) use ($callback, $that) {
    $resolver->resolve('github.com')->then(function($ip) {
        echo 'IP for github.com: ' . $ip . PHP_EOL;
    });
}]);
```

## Tests

To run the unit test suite:

```
curl -s https://getcomposer.org/installer | php
php composer.phar install
cd tests
../vendor/bin/phpunit
```

## License

Released under the MIT License. See `LICENSE`.
