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

use Phake;
use Phergie\Irc\Event\EventInterface;
use Phergie\Irc\Bot\React\EventQueueInterface;

/**
 * Tests for the Plugin class.
 *
 * @category Phergie
 * @package WyriHaximus\Phergie\Plugin\Dns
 */
class PluginTest extends \PHPUnit_Framework_TestCase
{

    public function testGetSubscribedEvents()
    {
        $plugin = new Plugin();
        $subscribedEvents = $plugin->getSubscribedEvents();
        $this->assertInternalType('array', $subscribedEvents);
        $this->assertSame(array(
                'command.dns' => 'handleDnsCommand'
        ), $subscribedEvents);
    }

    public function testGetSubscribedEventsCustomCommandName()
    {
        $plugin = new Plugin(array(
            'command' => 'dnsCustomName',
        ));
        $subscribedEvents = $plugin->getSubscribedEvents();
        $this->assertInternalType('array', $subscribedEvents);
        $this->assertSame(array(
            'command.dnsCustomName' => 'handleDnsCommand'
        ), $subscribedEvents);
    }

    /**
     * Tests that getSubscribedEvents() returns an array.
     */
    public function _testHandleDnsCommand()
    {
        $resolver = $this->getMock('React\Dns\Resolver\Resolver', array(
            'resolve',
        ), array(
            '8.8.8.8:53',
            $this->getMock('React\Dns\Query\ExecutorInterface'),
        ));

        $plugin = new Plugin(array(
            'resolver' => $resolver,
        ));
        $event = $this->getMock('Phergie\Irc\Plugin\React\Command\CommandEvent');
        $queue = $this->getMock('Phergie\Irc\Bot\React\EventQueueInterface');
        $plugin->handleDnsCommand($event, $queue);
    }
}
