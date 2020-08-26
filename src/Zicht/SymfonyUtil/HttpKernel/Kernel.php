<?php
/**
 * @copyright Zicht online <http://zicht.nl>
 */
namespace Zicht\SymfonyUtil\HttpKernel;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Kernel as SymfonyKernel;
use Zicht\Util\Str;

/**
 * Base kernel for Symfony apps, using the APPLICATION_ENV parameter and utilizing autoloading bundle configs.
 */
abstract class Kernel extends SymfonyKernel
{
    /**
     * The environments that behave in debugging mode by default
     *
     * @var array
     */
    public static $DEBUG_ENVS = ['development', 'testing'];

    /**
     * Early 404 detector patterns
     *
     * @var array
     */
    protected static $STATIC_CONTENT_PATTERN = [
        // pattern means: "/media/cache/[imagine_filter_name]/media..."
        '!^(?:/media/cache(?:/resolve)?/[^/]+)?/((?:media/|bundles/).*)!',
    ];

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
     * @param string $name
     */
    public function __construct($environment = null, $debug = null, $name = null)
    {
        umask(0);

        $this->name = $name ?: getenv('APPLICATION_NAME') ?: 'app';
        $environment = $environment ?: getenv('APPLICATION_ENV');
        $debug = (
            null === $debug
                ? in_array($environment, static::$DEBUG_ENVS)
                : $debug
        );

        parent::__construct($environment, $debug);

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

        if ($this->sessionConfig && @is_readable($this->getRootDir() . '/' . $this->sessionConfig)) {
            $loader->load($this->getRootDir() . '/' . $this->sessionConfig);
        }

        foreach ($this->getBundles() as $n => $bundle) {
            $bundleName = Str::uscore(Str::rstrip(Str::classname($n), 'Bundle'));

            if (is_file($fn = $appRoot . '/config/bundles/' . $bundleName . '.yml')) {
                $loader->load($fn);
            }
        }

        foreach ($this->getConfigFiles() as $configFile) {
            $loader->load($configFile);
        }
    }


    /**
     * Get all config files which should be loaded. Can be overridden for custom logic.
     *
     * By default, following files are loaded (relativy to `getRootDir()`)
     *
     * - if `config/config_local.yml` exists, load that.
     * - if `config/config_local.yml` does not exist, load `config/config_{environment}.yml`
     * - additionally: `config/kernel_{name}.yml`
     *
     * @return array
     */
    protected function getConfigFiles()
    {
        $ret = [];
        foreach ($this->getCandidateConfigFiles() as $configFile) {
            if (is_file($configFile)) {
                $ret[]= $configFile;
                break;
            }
        }
        $configFile = join(
            '/',
            [
                $this->getRootDir(),
                'config',
                sprintf('kernel_%s.yml', $this->getName())
            ]
        );
        if (is_file($configFile)) {
            $ret[]= $configFile;
        }
        return $ret;
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
        if ($this->lightweightContainer && $this->lightweightContainer->has('session')) {
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
        $container = $this->bootLightweightContainer();

        if ($this->sessionConfig && @is_readable($this->getRootDir() . '/' . $this->sessionConfig)) {
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
                            'name'          => $container->getParameter('session.name'),
                            'cookie_samesite' => $container->hasParameter('session.cookie_samesite') ? $container->getParameter('session.cookie_samesite') : null,
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
    }

    /**
     * Boot a lightweight container which can be used for early request handling
     *
     * @return null|Container
     */
    protected function bootLightweightContainer()
    {
        if (null === $this->lightweightContainer) {
            $this->lightweightContainer
                = $container
                = new Container();

            foreach ($this->getKernelParameters() as $param => $value) {
                $this->lightweightContainer->setParameter($param, $value);
            }
        }
        return $this->lightweightContainer;
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
            // prefer 'config_local.yml' if it exists
            $this->getRootDir() . '/config/config_local.yml',
            $this->getRootDir() . '/config/config_' . $this->getEnvironment() . '.yml'
        );
    }


    /**
     * Front controller for the command line interface (`app/console`). You can use doc/examples/console.phpcs as
     * template for `app/console`
     *
     * @param InputInterface|null $input
     *
     * @return int
     */
    final public function console(InputInterface $input)
    {
        if (!$input) {
            $input = new ArgvInput();
        }
        $application = new Application($this);
        return $application->run($input);
    }


    /**
     * Front controller for the web interface (`web/index.php`). You can use doc/examples/index.phps as a template
     * for your index.php
     *
     * @return void
     */
    final public function web()
    {
        $request = Request::createFromGlobals();
        $response = $this->handle($request);
        $response->send();
        $this->terminate($request, $response);
    }
}
