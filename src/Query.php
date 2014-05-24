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
 * Plugin for Looking up IP's by hostnames.
 *
 * @category Phergie
 * @package WyriHaximus\Phergie\Plugin\Dns
 */
class Query
{
    protected $resolveCallback;
    protected $rejectCallback;

    public function __construct(callable $resolveCallback, callable $rejectCallback) {
        $this->resolveCallback = $resolveCallback;
        $this->rejectCallback = $rejectCallback;
    }

    public function callResolve($ip) {
        $resolveCallback = $this->resolveCallback;
        return $resolveCallback($ip);
    }

    public function callReject($error) {
        $rejectCallback = $this->rejectCallback;
        return $rejectCallback($error);
    }
}
