<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\SymfonyUtil\HttpKernel;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class RestHandler
 *
 * @package Zicht\SymfonyUtil\HttpKernel
 */
class RestHandler implements HandlerInterface
{
    /**
     * Construct the routing handler. The base path is used to check the request uri, all of the routes are mounted
     * under the basePath and routed to the specified callables, if they match.
     *
     * @param string $basePath
     * @param array<string,string,callable> $routes
     */
    public function __construct($basePath, $routes)
    {
        $this->basePath = $basePath;
        $this->routes = $routes;
    }

    /**
     * Handles a request. Returns null if the request is not handled.
     *
     * @param Request $request
     * @return Response
     */
    public function handle(Request $request)
    {
        $pathInfo = $request->getPathInfo();

        if (0 === strpos($pathInfo, $this->basePath)) {
            foreach ($this->routes as $config) {
                list($method, $pattern, $controller) = $config;

                if ($request->getMethod() === strtoupper($method)
                    && preg_match('!^' . preg_quote($this->basePath . $pattern, '!') . '$!', $request->getPathInfo())
                ) {
                    return call_user_func($controller, $request);
                }
            }
        }

        return null;
    }
}
