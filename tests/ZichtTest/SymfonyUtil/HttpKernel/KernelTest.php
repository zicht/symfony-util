<?php

namespace ZichtTest\SymfonyUtil\HttpKernel;

use Zicht\SymfonyUtil\HttpKernel\Kernel;

class MyKernel extends Kernel
{
    public function registerBundles() {}
}

/**
 * @covers \Zicht\SymfonyUtil\HttpKernel\Kernel
 */
class KernelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider envs
     */
    function testEnvironment($env, $debug)
    {
        putenv("APPLICATION_ENV=$env");
        $kernel = new MyKernel();
        $this->assertEquals($env, $kernel->getEnvironment());
        $this->assertEquals($debug, $kernel->isDebug());
    }

    function envs() {
        return array(
            array('development', true),
            array('testing', true),
            array('staging', false),
            array('production', false),
        );
    }
}
