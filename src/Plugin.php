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
use Phergie\Irc\Event\UserEvent;
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
     * dnsServer - optional DNS Server's IP address to use for lookups, defaults to 8.8.8.8
     *
     * command - optional command name, can be used to setup separate multiple DNS resolvers, defaults to dns
     *
     * resolver - optional Resolver instance, defaults to calling the 'dns.resolver' event
     *
     * disableCommand - disable the command if you don't need it, defaults to false
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
     * Indicates that the plugin provides DNS resolving services.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        $events = array(
            $this->command . '.resolve' => 'resolveDnsQuery',
            $this->command . '.resolver' => 'getResolverEvent',
        );

        if (!$this->disableCommand) {
            $events['command.' . $this->command] = 'handleDnsCommand';
        }

        return $events;
    }

    public function logDebug($message) {
        $this->logger->debug('[Dns]' . $message);
    }

    public function handleDnsCommand(UserEvent $event, EventQueueInterface $queue)
    {
        if (get_class($event) !== '\Phergie\Irc\Plugin\React\Command\CommandEvent' && !is_subclass_of($event, '\Phergie\Irc\Plugin\React\Command\CommandEvent')) {
            throw new \BadMethodCallException(get_class($event) . ' given, expected: Phergie\Irc\Plugin\React\Command\CommandEvent');
        }

        foreach ($event->getCustomParams() as $hostname) {
            $message = $hostname . ': ';
            $this->logDebug('Looking up: ' . $hostname);
            $that = $this;
            $this->resolveDnsQuery($hostname, function($promise) use ($event, $queue, $message, $that) {
                $promise->then(function ($ip) use ($event, $queue, $message, $that) {
                    $message = $message . $ip;
                    $that->logDebug($message);
                    foreach ($event->getTargets() as $target) {
                        $queue->ircPrivmsg($target, $message);
                    }
                });
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
            $this->logDebug('Existing Resolver found using it');
            return $this->resolver;
        }

        if ($factory === null) {
            $factory = new Factory();
        }

        $this->logDebug('Creating new Resolver');

        $this->resolver = $factory->createCached($this->dnsServer, $this->loop);

        return $this->resolver;
    }

    /**
     * @param callable $callback
     */
    public function getResolverEvent($callback) {
        $this->logDebug($this->command . '.resolver called');
        $callback($this->getResolver());
    }

    /**
     * @param $hostname
     *
     * @return React\Promise\DeferredPromise
     */
    public function resolveDnsQuery($hostname, $callback) {
        $this->logDebug($this->command . '.resolve called for: ' . $hostname);
        $callback($this->getResolver()->resolve($hostname));
    }
    
}
