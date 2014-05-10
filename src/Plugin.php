<?php
/**
 * This file is part of PhergieDns.
 *
 ** (c) 2014 Cees-Jan Kiewiet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WyriHaximus\Phergie\Plugin\Dns;

use Phergie\Irc\Bot\React\AbstractPlugin;
use Phergie\Irc\Bot\React\EventQueueInterface;
use Phergie\Irc\Plugin\React\Command\CommandEvent;
use React\Dns\Resolver\Factory;
use React\Dns\Resolver\Resolver;

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
        if (isset($config['resolver']) && $config['resolver'] instanceof Resolver) {
            $this->resolver = $config['resolver'];
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
            $this->command . '.resolve' => 'resolveDnsQuery',
            $this->command . '.resolver' => 'getResolver',
        );
    }

    public function handleDnsCommand(CommandEvent $event, EventQueueInterface $queue)
    {
        foreach ($event->getCustomParams() as $hostname) {
            $message = $hostname . ': ';
            $this->logger->debug('Looking up: ' . $hostname);
            $logger = $this->logger;
            $this->resolveDnsQuery($hostname)->then(function ($ip) use ($event, $queue, $message, $logger) {
                $message = $message . $ip;
                $logger->debug($message);
                foreach ($event->getTargets() as $target) {
                    $queue->ircPrivmsg($target, $message);
                }
            });
        }
    }

    public function getResolver(Factory $factory = null) {
        if ($this->resolver instanceof Resolver) {
            return $this->resolver;
        }

        if ($factory === null) {
            $factory = new Factory();
        }

        $this->resolver = $factory->createCached($this->dnsServer, $this->loop);

        return $this->resolver;
    }

    public function resolveDnsQuery($hostname) {
        return $this->getResolver()->resolve($hostname);
    }
    
}
