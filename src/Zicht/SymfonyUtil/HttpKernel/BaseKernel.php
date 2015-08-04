<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht online <http://zicht.nl>
 */
namespace Zicht\SymfonyUtil\HttpKernel;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Kernel;
use Zicht\Util\Str;

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
        // pattern means: "/media/cache/[imagine_filter_name]/media..."
        '!^(?:/media/cache(?:/resolve)?/[^/]+)?/((?:media|bundles).*)!',
    );

    protected $sessionConfig = 'config/session.php';

    /**
     * @var HandlerInterface[]
     */
    protected $earlyHandlers = array();

    /**
     * @var null
     */
    protected $lightweightContainer = null;


    /**
     * Overrides the default constructor to use APPLICATION_ENV and default debugging.
     *
     * @param string $environment
     * @param bool $debug
     */
    public function __construct($environment = null, $debug = null)
    {
        $environment   = $environment ?: getenv('APPLICATION_ENV');

        $debug = (
            null === $debug
            ? in_array($environment, self::$DEBUG_ENVS)
            : $debug
        );

        parent::__construct($environment, $debug);
        umask(0);

        $this->earlyHandlers = $this->registerEarlyHandlers();
    }


    /**
     * Returns an array of handlers that can deliver a response based on the request without booting the kernel
     *
     * @return array
     */
    protected function registerEarlyHandlers()
    {
        return array(
            new StaticMediaNotFoundHandler(static::$STATIC_CONTENT_PATTERN, $this->getWebDir())
        );
    }


    /**
     * @{inheritDoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $appRoot     = $this->getRootDir();

        $loader->load($appRoot . '/config/session.php');

        foreach ($this->getBundles() as $n => $bundle) {
            $bundleName = Str::uscore(Str::rstrip(Str::classname($n), 'Bundle'));

            if (is_file($fn = $appRoot . '/config/bundles/' . $bundleName . '.yml')) {
                $loader->load($fn);
            }
        }

        // prefer 'config_local.yml' if it exists
        foreach ($this->getCandidateConfigFiles() as $configFile) {
            if (is_file($configFile)) {
                $loader->load($configFile);
                break;
            }
        }
    }


    /**
     * @{inheritDoc}
     */
    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        // do an early request handling for handlers that don't need the service container.
        if ($type === self::MASTER_REQUEST) {
            $this->attachSession($request);

            foreach ($this->earlyHandlers as $handler) {
                if ($response = $handler->handle($request)) {
                    return $response;
                }
            }
        }
        $ret = parent::handle($request, $type, $catch);
        return $ret;
    }

    /**
     * @{inheritDoc}
     */
    public function boot()
    {
        parent::boot();
        if ($this->lightweightContainer->has('session')) {
            $this->container->set('session', $this->lightweightContainer->get('session'));
        }
    }


    /**
     * Attach a session to the request.
     *
     * @param Request $request
     * @return void
     */
    protected function attachSession(Request $request)
    {
        // TODO consider generating this code based on the ContainerBuilder / PhpDumper from Symfony DI.
        $this->lightweightContainer
            = $container
            = new Container();

        foreach ($this->getKernelParameters() as $param => $value) {
            $this->lightweightContainer->setParameter($param, $value);
        }

        require_once $this->getRootDir() . '/' . $this->sessionConfig;

        if ($request->cookies->has($container->getParameter('session.name'))) {
            if (is_readable($this->getCacheDir() . '/classes.php')) {
                require_once $this->getCacheDir() . '/classes.php';
            }
            $class = $container->getParameter('session.handler.class');

            $session = new Session\Session(
                new Session\Storage\NativeSessionStorage(
                    array(
                        'cookie_path'   => $container->getParameter('session.cookie_path'),
                        'cookie_domain' => $container->getParameter('session.cookie_domain'),
                        'name'          => $container->getParameter('session.name')
                    ),
                    new $class($container->getParameter('session.handler.save_path')),
                    new Session\Storage\MetadataBag()
                ),
                new Session\Attribute\AttributeBag(),
                new Session\Flash\FlashBag()
            );
            $this->lightweightContainer->set('session', $session);
            $request->setSession($session);
        }
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


    /**
     * Returns the file names that are candidate for the main configuration.
     *
     * @return array
     */
    protected function getCandidateConfigFiles()
    {
        return array(
            $this->getRootDir() . '/config/config_local.yml',
            $this->getRootDir() . '/config/config_' . $this->getEnvironment() . '.yml'
        );
    }
}
