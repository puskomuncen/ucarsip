<?php

namespace PHPMaker2025\ucarsip;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * PhpDebugBar middleware
 *
 * Based on PhpMiddleware\PhpDebugBarMiddleware
 */
class PhpDebugBarMiddleware implements MiddlewareInterface
{
    // Supported API routes
    public static array $ApiRoutes = [
        'api.twofa',
        'api.chat',
        'api.push',
        'api.chart',
        'api.lookup',
        'api.session',
        'api.jupload',
        'api.export'
    ];

    public function __construct(protected PhpDebugBar $debugBar)
    {
    }

    protected function isRedirect(Response $response): bool
    {
        $statusCode = $response->getStatusCode();
        return $statusCode >= 300 && $statusCode < 400 && $response->getHeaderLine('Location') !== '';
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        $response = $handler->handle($request);
        if (!IsDebug()) {
            return $response;
        }
        if (IsExport('print')) {
            $this->debugBar->stackData();
            return $response;
        } elseif (IsExport()) {
            return $response; // Header sent by AbstractExportBase
        }
        $routeName = RouteName();
        if (IsApi() && !in_array($routeName, self::$ApiRoutes)) {
            return $response;
        }
        if ($this->isRedirect($response)) {
            $this->debugBar->stackData();
        } elseif ($this->isHtmlResponse($response)) {
            if (
                $request->getParam('modal') // Modal
                || $request->getParam('d') // Drilldown
                || str_starts_with($routeName, 'preview.') // Preview page
                || str_starts_with($routeName, 'calendar.') // Calendar page
            ) {
                $this->debugBar->sendDataInHeaders();
            }
        } else {
            $this->debugBar->sendDataInHeaders();
        }
        return $response;
    }

    protected function isHtmlResponse(Response $response): bool
    {
        return strpos($response->getHeaderLine('Content-Type'), 'text/html') !== false;
    }
}
