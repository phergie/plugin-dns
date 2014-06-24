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
            'dns.resolve' => 'resolveDnsQuery',
            'dns.resolver' => 'getResolverEvent',
            'command.dns' => 'handleDnsCommand',
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
            'dnsCustomName.resolve' => 'resolveDnsQuery',
            'dnsCustomName.resolver' => 'getResolverEvent',
            'command.dnsCustomName' => 'handleDnsCommand',
        ), $subscribedEvents);
    }

    public function testGetSubscribedEventsDisabledCommand()
    {
        $plugin = new Plugin(array(
            'disableCommand' => true,
        ));
        $subscribedEvents = $plugin->getSubscribedEvents();
        $this->assertInternalType('array', $subscribedEvents);
        $this->assertSame(array(
                'dns.resolve' => 'resolveDnsQuery',
                'dns.resolver' => 'getResolverEvent',
            ), $subscribedEvents);
    }

    public function testHandleDnsCommand()
    {
        $resolver = $this->getMock('React\Dns\Resolver\Resolver', array(
            'resolve',
        ), array(
            '8.8.8.8:53',
            $this->getMock('React\Dns\Query\ExecutorInterface'),
        ));
        $deferred = new \React\Promise\Deferred();
        $resolver->expects($this->once())
            ->method('resolve')
            ->with('wyrihaximus.net')
            ->willReturn($deferred->promise());

        $plugin = new Plugin(array(
            'resolver' => $resolver,
        ));

        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $plugin->setLogger($logger);

        $event = $this->getMock('Phergie\Irc\Plugin\React\Command\CommandEvent', array(
            'getCustomParams',
            'getTargets',
        ));
        $event->expects($this->once())
            ->method('getCustomParams')
            ->with()
            ->willReturn(array(
                'wyrihaximus.net',
            ));
        $event->expects($this->once())
            ->method('getTargets')
            ->with()
            ->willReturn(array(
                'WyriHaximus',
            ));

        $queue = $this->getMock('Phergie\Irc\Bot\React\EventQueueInterface', array(
            'ircPrivmsg',
            'extract',
            'setPrefix',
            'ircPass',
            'ircNick',
            'ircUser',
            'ircServer',
            'ircOper',
            'ircQuit',
            'ircJoin',
            'ircPart',
            'ircMode',
            'ircSquit',
            'ircTopic',
            'ircNames',
            'ircList',
            'ircInvite',
            'ircKick',
            'ircVersion',
            'ircStats',
            'ircLinks',
            'ircTime',
            'ircConnect',
            'ircTrace',
            'ircAdmin',
            'ircInfo',
            'ircNotice',
            'ircWho',
            'ircWhois',
            'ircWhowas',
            'ircKill',
            'ircPing',
            'ircPong',
            'ircError',
            'ircAway',
            'ircRehash',
            'ircRestart',
            'ircSummon',
            'ircUsers',
            'ircWallops',
            'ircUserhost',
            'ircIson',
            'ctcpFinger',
            'ctcpFingerResponse',
            'ctcpVersion',
            'ctcpVersionResponse',
            'ctcpSource',
            'ctcpSourceResponse',
            'ctcpUserinfo',
            'ctcpUserinfoResponse',
            'ctcpClientinfo',
            'ctcpClientinfoResponse',
            'ctcpErrmsg',
            'ctcpErrmsgResponse',
            'ctcpPing',
            'ctcpPingResponse',
            'ctcpTime',
            'ctcpTimeResponse',
            'ctcpAction',
            'ctcpActionResponse',
            'current',
            'next',
            'key',
            'valid',
            'rewind',
            'count',
        ));
        $queue->expects($this->once())
            ->method('ircPrivmsg')
            ->with('WyriHaximus', 'wyrihaximus.net: 1.2.3.4');

        $plugin->handleDnsCommand($event, $queue);

        $deferred->resolve('1.2.3.4');
    }

    public function testHandleDnsCommandError()
    {
        $resolver = $this->getMock('React\Dns\Resolver\Resolver', array(
            'resolve',
        ), array(
            '8.8.8.8:53',
            $this->getMock('React\Dns\Query\ExecutorInterface'),
        ));
        $deferred = new \React\Promise\Deferred();
        $resolver->expects($this->once())
            ->method('resolve')
            ->with('wyrihaximus.net')
            ->willReturn($deferred->promise());

        $plugin = new Plugin(array(
            'resolver' => $resolver,
        ));

        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $plugin->setLogger($logger);

        $event = $this->getMock('Phergie\Irc\Plugin\React\Command\CommandEvent', array(
            'getCustomParams',
            'getTargets',
        ));
        $event->expects($this->once())
            ->method('getCustomParams')
            ->with()
            ->willReturn(array(
                'wyrihaximus.net',
            ));
        $event->expects($this->once())
            ->method('getTargets')
            ->with()
            ->willReturn(array(
                'WyriHaximus',
            ));

        $queue = $this->getMock('Phergie\Irc\Bot\React\EventQueueInterface', array(
            'ircPrivmsg',
            'extract',
            'setPrefix',
            'ircPass',
            'ircNick',
            'ircUser',
            'ircServer',
            'ircOper',
            'ircQuit',
            'ircJoin',
            'ircPart',
            'ircMode',
            'ircSquit',
            'ircTopic',
            'ircNames',
            'ircList',
            'ircInvite',
            'ircKick',
            'ircVersion',
            'ircStats',
            'ircLinks',
            'ircTime',
            'ircConnect',
            'ircTrace',
            'ircAdmin',
            'ircInfo',
            'ircNotice',
            'ircWho',
            'ircWhois',
            'ircWhowas',
            'ircKill',
            'ircPing',
            'ircPong',
            'ircError',
            'ircAway',
            'ircRehash',
            'ircRestart',
            'ircSummon',
            'ircUsers',
            'ircWallops',
            'ircUserhost',
            'ircIson',
            'ctcpFinger',
            'ctcpFingerResponse',
            'ctcpVersion',
            'ctcpVersionResponse',
            'ctcpSource',
            'ctcpSourceResponse',
            'ctcpUserinfo',
            'ctcpUserinfoResponse',
            'ctcpClientinfo',
            'ctcpClientinfoResponse',
            'ctcpErrmsg',
            'ctcpErrmsgResponse',
            'ctcpPing',
            'ctcpPingResponse',
            'ctcpTime',
            'ctcpTimeResponse',
            'ctcpAction',
            'ctcpActionResponse',
            'current',
            'next',
            'key',
            'valid',
            'rewind',
            'count',
        ));
        $queue->expects($this->once())
            ->method('ircPrivmsg')
            ->with('WyriHaximus', 'wyrihaximus.net: error looking up hostname: Error');

        $plugin->handleDnsCommand($event, $queue);

        $deferred->reject(new \Exception('Error'));
    }

    public function testGetResolver()
    {
        $plugin = new Plugin(array(
            'dnsServer' => '4.3.2.1',
        ));

        $plugin->setLoop($this->getMock('React\EventLoop\LoopInterface'));
        $plugin->setLogger($this->getMock('Psr\Log\LoggerInterface'));

        $factory = $this->getMock('React\Dns\Resolver\Factory', array(
            'createCached',
        ));
        $factory->expects($this->once())
            ->method('createCached')
            ->with('4.3.2.1')
            ->willReturn('foo:bar');

        $this->assertSame('foo:bar', $plugin->getResolver($factory));
    }

    public function testGetResolverBare()
    {
        $plugin = new Plugin(array(
            'dnsServer' => '4.3.2.1',
        ));

        $plugin->setLoop($this->getMock('React\EventLoop\LoopInterface'));
        $plugin->setLogger($this->getMock('Psr\Log\LoggerInterface'));

        $this->assertInstanceOf('React\Dns\Resolver\Resolver', $plugin->getResolver());
    }

    public function testGetResolverEvent()
    {
        $plugin = new Plugin();

        $plugin->setLoop($this->getMock('React\EventLoop\LoopInterface'));
        $plugin->setLogger($this->getMock('Psr\Log\LoggerInterface'));

        $callbackFired = false;
        $that = $this;
        $callback = function($resolver) use (&$callbackFired, $that) {
            $that->assertInstanceOf('React\Dns\Resolver\Resolver', $resolver);
            $callbackFired = true;
        };

        $plugin->getResolverEvent($callback);
        $this->assertTrue($callbackFired);
    }

    public function testResolveDnsQuery()
    {
        $resolver = $this->getMock('React\Dns\Resolver\Resolver', array(
            'resolve',
        ), array(
            '8.8.8.8:53',
            $this->getMock('React\Dns\Query\ExecutorInterface'),
        ));
        $deferred = new \React\Promise\Deferred();
        $resolver->expects($this->once())
            ->method('resolve')
            ->with('wyrihaximus.net')
            ->willReturn($deferred->promise());

        $plugin = new Plugin(array(
            'resolver' => $resolver,
        ));

        $plugin->setLogger($this->getMock('Psr\Log\LoggerInterface'));

        $callbackFired = false;
        $that = $this;
        $callback = function($ip) use (&$callbackFired, $that) {
            $that->assertSame('1.2.3.4', $ip);
            $callbackFired = true;
        };

        $plugin->resolveDnsQuery(new Query('wyrihaximus.net', $callback, function() {}));
        $deferred->resolve('1.2.3.4');
        $this->assertTrue($callbackFired);
    }

    public function testRejectDnsQuery()
    {
        $resolver = $this->getMock('React\Dns\Resolver\Resolver', array(
            'resolve',
        ), array(
            '8.8.8.8:53',
            $this->getMock('React\Dns\Query\ExecutorInterface'),
        ));
        $deferred = new \React\Promise\Deferred();
        $resolver->expects($this->once())
            ->method('resolve')
            ->with('wyrihaximus.net')
            ->willReturn($deferred->promise());

        $plugin = new Plugin(array(
            'resolver' => $resolver,
        ));

        $plugin->setLogger($this->getMock('Psr\Log\LoggerInterface'));

        $callbackFired = false;
        $that = $this;
        $callback = function($error) use (&$callbackFired, $that) {
            $that->isInstanceOf('Exception', $error);
            $callbackFired = true;
        };

        $plugin->resolveDnsQuery(new Query('wyrihaximus.net', function() {}, $callback));
        $deferred->reject(new \Exception('Error'));
        $this->assertTrue($callbackFired);
    }
}
