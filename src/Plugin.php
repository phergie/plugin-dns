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
use Phergie\Irc\Client\React\LoopAwareInterface;
use Phergie\Irc\Plugin\React\Command\CommandEvent;
use React\Dns\Resolver\Factory;
use React\Dns\Resolver\Resolver;
use React\EventLoop\LoopInterface;

/**
 * Plugin for Looking up IP's by hostnames.
 *
 * @category Phergie
 * @package WyriHaximus\Phergie\Plugin\Dns
 */
class Plugin extends AbstractPlugin implements LoopAwareInterface
{
    protected $resolver;

    protected $dnsServer = '8.8.8.8';
    protected $command = 'dns';
    protected $disableCommand = false;

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
        if (isset($config['disableCommand'])) {
            $this->disableCommand = $config['disableCommand'];
        }
    }

    public function setLoop(LoopInterface $loop) {
        $this->loop = $loop;
    }

    /**
     *
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        $events = array(
            $this->command . '.resolve' => 'resolveDnsQuery',
            $this->command . '.resolver' => 'getResolver',
        );

        if (!$this->disableCommand) {
            $events['command.' . $this->command] = 'handleDnsCommand';
        }

        return $events;
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

    /**
     * @param Factory $factory
     *
     * @return Resolver
     */
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

    /**
     * @param $hostname
     *
     * @return React\Promise\DeferredPromise
     */
    public function resolveDnsQuery($hostname) {
        return $this->getResolver()->resolve($hostname);
    }
    
}
