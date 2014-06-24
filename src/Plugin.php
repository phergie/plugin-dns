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

    public function handleDnsCommand(CommandEvent $event, EventQueueInterface $queue)
    {
        foreach ($event->getCustomParams() as $hostname) {
            $this->logDebug('Looking up: ' . $hostname);
            $that = $this;
            $this->resolveDnsQuery(new Query($hostname, function($ip, $hostname) use ($event, $queue, $that) {
                $message = $hostname . ': ' . $ip;
                $that->logDebug($message);
                foreach ($event->getTargets() as $target) {
                    $queue->ircPrivmsg($target, $message);
                }
            }, function($error, $hostname) use ($event, $queue, $that) {
                $message = $hostname . ': error looking up hostname: ' . $error->getMessage();
                $that->logDebug($message);
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
     * @param Query $query
     */
    public function resolveDnsQuery(Query $query) {
        $that = $this;
        $this->logDebug($this->command . '.resolve called for: ' . $query->getHostname());
        $this->getResolver()->resolve($query->getHostname())->then(function($ip) use ($that, $query) {
            $that->logDebug('IP for hostname ' . $query->getHostname() . ' found: ' . $ip);
            $query->callResolve($ip);
        }, function($error) use ($that, $query) {
            $that->logDebug('IP for hostname ' . $query->getHostname() . ' not found: ' . $error->getMessage());
            $query->callReject($error);
        });
    }
    
}
