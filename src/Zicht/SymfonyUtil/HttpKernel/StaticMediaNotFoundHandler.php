<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\SymfonyUtil\HttpKernel;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class StaticMediaNotFoundHandler
 *
 * @package Zicht\SymfonyUtil\HttpKernel
 */
class StaticMediaNotFoundHandler implements HandlerInterface
{
    /**
     * Construct the handler with the passed patterns and webdir as configuration.
     *
     * The pattern should contain regular expressions containing the path name to check in matching group '1', e.g.:
     *
     * '!^((?:media|bundles)/.*)!'
     *
     * If you need dirs and symlinks to resolve, you need a different implementation, or amend this implementation
     * with a configuration parameter.
     *
     * @param array $staticContentPatterns
     * @param string $webDir
     */
    public function __construct(array $staticContentPatterns, $webDir)
    {
        $this->staticContentPatterns = $staticContentPatterns;
        $this->webDir = $webDir;
    }


    /**
     * Handles a request. Returns null if the request is not handled.
     *
     * @param Request $request
     * @return Response
     */
    public function handle(Request $request)
    {
        foreach ($this->staticContentPatterns as $ptn) {
            if (preg_match($ptn, $request->getRequestUri(), $m)) {
                $path = urldecode(parse_url($m[1], PHP_URL_PATH));
                if (!is_file($this->webDir . $path)) {
                    return $this->createDefaultNotFoundResponse($path);
                }
            }
        }
        return null;
    }

    /**
     * Creates the default 404 response for early 404 detection
     *
     * @param string $path
     * @return Response
     */
    protected function createDefaultNotFoundResponse($path)
    {
        return new Response(sprintf('File not found: %s', htmlspecialchars($path, ENT_QUOTES)), 404);
    }
}
