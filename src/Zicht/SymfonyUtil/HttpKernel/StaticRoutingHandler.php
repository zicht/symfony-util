<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\SymfonyUtil\HttpKernel;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The static routing handler can be used to route requests before booting the service container to lightweight
 * controllers, i.e. controllers that either handle their own dependencies or they have none and thus don't need to
 * boot the service container.
 *
 * @package Zicht\SymfonyUtil\HttpKernel
 */
class StaticRoutingHandler implements HandlerInterface
{
    /**
     * Construct the routing handler. The base path is used to check the request uri, all of the routes are mounted
     * under the basePath and routed to the specified callables, if they match.
     *
     * @param string $basePath
     * @param array<string, callable> $routes
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
        $requestUri = $request->getRequestUri();

        if (0 === strpos($requestUri, $this->basePath)) {
            foreach ($this->routes as $pattern => $controller) {
                if (preg_match('!' . preg_quote($this->basePath . $pattern, '!') . '!A', $requestUri)) {
                    return call_user_func($controller, $request);
                }
            }
        }

        return null;
    }
}