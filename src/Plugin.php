<?php
/*
 * This file is part of PhergieDns.
 *
 ** (c) 2013 - 2014 Cees-Jan Kiewiet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WyriHaximus\Phergie\Plugin\Dns;

use Phergie\Irc\Bot\React\AbstractPlugin;
use Phergie\Irc\Bot\React\EventQueueInterface;
use Phergie\Irc\Plugin\React\Command\CommandEvent;
use React\Dns\Resolver\Factory;

/**
 * Plugin for Looking up IP&#039;s by hostnames.
 *
 * @category Phergie
 * @package WyriHaximus\Phergie\Plugin\Dns
 */
class Plugin extends AbstractPlugin
{
    protected $resolver;

    protected $dnsServer = '8.8.8.8';
    protected $command = 'dns';

    /**
     * Accepts plugin configuration.
     *
     * Supported keys:
     *
     *
     *
     * @param array $config
     */
    public function __construct(array $config = array())
    {
        if (isset($config['dnsServer'])) {
            $this->dnsServer = $config['dnsServer'];
        }
        if (isset($config['command'])) {
            $this->command = $config['command'];
        }
    }

    /**
     *
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            'command.' . $this->command => 'handleDnsCommand',
        );
    }

    public function handleDnsCommand(CommandEvent $event, EventQueueInterface $queue)
    {
        foreach ($event->getCustomParams() as $hostname) {
            $message = $hostname . ': ';
            $this->logger->debug('Looking up: ' . $hostname);
            $logger = $this->logger;
            $this->getResolver()->resolve($hostname)->then(function ($ip) use ($event, $queue, $message, $logger) {
                $message = $message . $ip;
                $logger->debug($message);
                foreach ($event->getTargets() as $target) {
                    $queue->ircPrivmsg($target, $message);
                }
            });
        }
    }

    public function getResolver() {
        if ($this->resolver !== null) {
            return $this->resolver;
        }

        $factory = new Factory();
        $this->resolver = $factory->createCached($this->dnsServer, $this->loop);

        return $this->resolver;
    }

    
}
