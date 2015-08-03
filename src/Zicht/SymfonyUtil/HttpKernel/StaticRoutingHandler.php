<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\SymfonyUtil\HttpKernel;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class StaticRoutingHandler implements HandlerInterface
{
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