<?php
/*
 * @copyright Zicht online <http://zicht.nl>
 */

namespace Zicht\SymfonyUtil\HttpFoundation;

/**
 * Driver for the redis session save handler provided by the redis PHP extension.
 * Copied from the drak/zikula nativesession library, which ceased to exist.
 */
class RedisSessionHandler extends \SessionHandler
{
    /**
     * @param string $savePath Path of redis server.
     */
    public function __construct($savePath = 'tcp://127.0.0.1:6379?persistent=0')
    {
        if (!extension_loaded('redis')) {
            throw new \RuntimeException('PHP does not have "redis" session module registered');
        }

        if (null === $savePath) {
            $savePath = ini_get('session.save_path');
        }

        ini_set('session.save_handler', 'redis');
        ini_set('session.save_path', $savePath);
    }
}
