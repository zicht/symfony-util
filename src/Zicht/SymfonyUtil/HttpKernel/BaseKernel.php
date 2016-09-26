<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht online <http://zicht.nl>
 */
namespace Zicht\SymfonyUtil\HttpKernel;

/**
 * Base kernel for Symfony apps, using the APPLICATION_ENV parameter and utilizing autoloading bundle configs.
 *
 * @deprecated: Please extend Kernel in stead. Add "app" as first parameter for backward compatibility.
 */
abstract class BaseKernel extends Kernel
{
    public function __construct($environment = null, $debug = null)
    {
        parent::__construct('app', $environment, $debug);
    }
}
