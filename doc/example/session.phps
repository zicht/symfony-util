<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

/** @var \Symfony\Component\DependencyInjection\ContainerInterface $container */

$params = [
    'session.name'                  => 'PHPSESSID',
    'session.cookie_path'           => '/',
    'session.cookie_domain'         => '',
    'session.handler.class'         => 'Drak\NativeSession\NativeRedisSessionHandler',
    'session.handler.save_path'     => 'tcp://localhost:6379?timeout=2'
];

foreach ($params as $key => $value) {
    $container->setParameter($key, $value);
}
