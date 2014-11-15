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
use Phergie\Irc\Plugin\React\Command\CommandEventInterface;
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

    /**
     * @var null|Resolver
     */
    protected $resolver;

    /**
     * @var LoopInterface
     */
    protected $loop;

    /**
     * @var string
     */
    protected $dnsServer = '8.8.8.8';

    /**
     * @var string
     */
    protected $command = 'dns';

    /**
     * @var bool
     */
    protected $enableCommand = false;

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
     * enableCommand - enable the command if you want to use it, defaults to false
     *
     * @param array $config
     */
    public function __construct(array $config = [])
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
        if (isset($config['enableCommand'])) {
            $this->enableCommand = $config['enableCommand'];
        }
    }

    /**
     * @param LoopInterface $loop
     */
    public function setLoop(LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    /**
     * Indicates that the plugin provides DNS resolving services.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        $events = [
            $this->command . '.resolve' => 'resolveDnsQuery',
            $this->command . '.resolver' => 'getResolverEvent',
        ];

        if ($this->enableCommand) {
            $events['command.' . $this->command] = 'handleDnsCommand';
        }

        return $events;
    }

    /**
     * @param string $message
     */
    public function logDebug($message)
    {
        $this->logger->debug('[Dns]' . $message);
    }

    /**
     * @param CommandEventInterface $event
     * @param EventQueueInterface $queue
     *
     * @throws \BadMethodCallException
     */
    public function handleDnsCommand(CommandEventInterface $event, EventQueueInterface $queue)
    {
        if (get_class($event) !== '\Phergie\Irc\Plugin\React\Command\CommandEvent' && !is_subclass_of($event, '\Phergie\Irc\Plugin\React\Command\CommandEvent')) {
            throw new \BadMethodCallException(get_class($event) . ' given, expected: Phergie\Irc\Plugin\React\Command\CommandEvent');
        }

        foreach ($event->getCustomParams() as $hostname) {
            $this->logDebug('Looking up: ' . $hostname);
            $this->resolveDnsQuery(new Query($hostname, function($ip, $hostname) use ($event, $queue) {
                $message = $hostname . ': ' . $ip;
                $this->logDebug($message);
                foreach ($event->getTargets() as $target) {
                    $queue->ircPrivmsg($target, $message);
                }
            }, function($error, $hostname) use ($event, $queue) {
                $message = $hostname . ': error looking up hostname: ' . $error->getMessage();
                $this->logDebug($message);
                foreach ($event->getTargets() as $target) {
                    $queue->ircPrivmsg($target, $message);
                }
            }));
        }
    }

    /**
     * @param Factory $factory
     *
     * @return Resolver
     */
    public function getResolver(Factory $factory = null)
    {
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
    public function getResolverEvent($callback)
    {
        $this->logDebug($this->command . '.resolver called');
        $callback($this->getResolver());
    }

    /**
     * @param Query $query
     */
    public function resolveDnsQuery(Query $query) {
        $this->logDebug($this->command . '.resolve called for: ' . $query->getHostname());
        $this->getResolver()->resolve($query->getHostname())->then(function($ip) use ($query) {
            $this->logDebug('IP for hostname ' . $query->getHostname() . ' found: ' . $ip);
            $query->callResolve($ip);
        }, function($error) use ($query) {
            $this->logDebug('IP for hostname ' . $query->getHostname() . ' not found: ' . $error->getMessage());
            $query->callReject($error);
        });
    }

}
