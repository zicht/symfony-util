<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht online <http://zicht.nl>
 */
namespace Zicht\SymfonyUtil\HttpKernel;

use \Symfony\Component\Config\Loader\LoaderInterface;
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;
use \Symfony\Component\HttpKernel\HttpKernelInterface;
use \Symfony\Component\HttpKernel\Kernel;
use \Zicht\Util\Str;

/**
 * Base kernel for Symfony apps, using the APPLICATION_ENV parameter and utilizing autoloading bundle configs.
 */
abstract class BaseKernel extends Kernel
{
    /**
     * The environments that behave in debugging mode by default
     *
     * @var array
     */
    public static $DEBUG_ENVS = array('development', 'testing');

    /**
     * Early 404 detector patterns
     *
     * @var array
     */
    protected static $STATIC_CONTENT_PATTERN = array(
        '!^(?:/media/cache/[^/]+)?/(media.*)!',
        '!^(/bundles/.*)!'
    );

    /**
     * Overrides the default constructor to use APPLICATION_ENV and default debugging.
     *
     * @param string $environment
     * @param bool $debug
     */
    public function __construct($environment = null, $debug = null)
    {
        $environment = $environment ?: getenv('APPLICATION_ENV');

        $debug = (
            null === $debug
            ? in_array($environment, self::$DEBUG_ENVS)
            : $debug
        );

        parent::__construct($environment, $debug);
        umask(0);
    }


    /**
     * @{inheritDoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $appRoot = $this->getRootDir();

        foreach ($this->getBundles() as $n => $bundle) {
            $bundleName = Str::uscore(Str::rstrip(Str::classname($n), 'Bundle'));

            if (is_file($fn = $appRoot . '/config/bundles/' . $bundleName . '.yml')) {
                $loader->load($fn);
            }
        }

        $loader->load($appRoot . '/config/config_' . $this->getEnvironment() . '.yml');
    }


    /**
     * @{inheritDoc}
     */
    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        // do an early 404 detection for assets and media
        if ($type === self::MASTER_REQUEST) {
            foreach (self::$STATIC_CONTENT_PATTERN as $ptn) {
                if (preg_match($ptn, $request->getRequestUri(), $m)) {
                    if (!is_file($this->getWebDir() . $m[1])) {
                        return $this->createDefaultNotFoundResponse($m[1]);
                    }
                }
            }
        }
        return parent::handle($request, $type, $catch);
    }


    /**
     * Creates the default 404 response for early 404 detection
     *
     * @param string $path
     * @return Response
     */
    protected function createDefaultNotFoundResponse($path)
    {
        return new Response('File not found: ' . $path, 404);
    }


    /**
     * Returns the path to the web dir.
     *
     * @return string
     */
    public function getWebDir()
    {
        return $this->getRootDir() . '/../web/';
    }
}
