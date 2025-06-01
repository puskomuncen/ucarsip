<?php

namespace PHPMaker2025\ucarsip;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\InvocationStrategyInterface;

/**
 * Route callback strategy with route parameters as an array of arguments
 */
class RequestResponseStrategy implements InvocationStrategyInterface
{
    /**
     * Invoke a route callable with request, response, and all route parameters
     * as an array of arguments.
     *
     * @param array<string, string>  $routeArguments
     */
    public function __invoke(
        callable $callable,
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $routeArguments
    ): ResponseInterface {
        foreach ($routeArguments as $k => $v) {
            $request = $request->withAttribute($k, $v);
        }

        // Set up global request and response
        $GLOBALS['Request'] = $request;
        $GLOBALS['Response'] = &$response;
        return $callable($request, $response, $routeArguments);
    }
}
