# wyrihaximus/phergie-dns

[Phergie](http://github.com/phergie/phergie-irc-bot-react/) plugin for Looking up IP&#039;s by hostnames.

[![Build Status](https://secure.travis-ci.org/WyriHaximus/PhergieDns.png?branch=master)](http://travis-ci.org/WyriHaximus/PhergieDns)

## Install

The recommended method of installation is [through composer](http://getcomposer.org).

```JSON
{
    "require": {
        "wyrihaximus/phergie-dns": "dev-master"
    }
}
```

See Phergie documentation for more information on
[installing and enabling plugins](https://github.com/phergie/phergie-irc-bot-react/wiki/Usage#plugins).

## Configuration

```php
new \WyriHaximus\Phergie\Plugin\Dns\Plugin(array(

    // All configuration is optional

    'dnsServer' => '1.2.3.4', // IP address of a DNS server, defaults to Google's 8.8.8.8

    // or

    'command' => 'customDns', // command name, defaults to dns

    // or

    'resolver' => new React\Dns\Resolver\Resolver(), // provide your own Resolver instance
                                                     // defaults to null and is set at first use

))
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
