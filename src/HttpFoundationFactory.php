<?php

namespace PHPMaker2025\ucarsip;

use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory as BaseHttpFoundationFactory;
use Psr\Http\Message\ServerRequestInterface as ServerRequestInterface;
use Symfony\Component\HttpFoundation\Request as Request;

/**
 * HttpFoundationFactory without files
 */
class HttpFoundationFactory extends BaseHttpFoundationFactory
{

    public function createRequest(ServerRequestInterface $psrRequest, bool $streamed = false): Request
    {
        $server = [];
        $uri = $psrRequest->getUri();
        if ($uri instanceof UriInterface) {
            $server['SERVER_NAME'] = $uri->getHost();
            $server['SERVER_PORT'] = $uri->getPort() ?: ('https' === $uri->getScheme() ? 443 : 80);
            $server['REQUEST_URI'] = $uri->getPath();
            $server['QUERY_STRING'] = $uri->getQuery();
            if ('' !== $server['QUERY_STRING']) {
                $server['REQUEST_URI'] .= '?' . $server['QUERY_STRING'];
            }
            if ('https' === $uri->getScheme()) {
                $server['HTTPS'] = 'on';
            }
        }
        $server['REQUEST_METHOD'] = $psrRequest->getMethod();
        $server = array_replace($psrRequest->getServerParams(), $server);
        $parsedBody = $psrRequest->getParsedBody();
        $parsedBody = \is_array($parsedBody) ? $parsedBody : [];
        $request = new Request(
            $psrRequest->getQueryParams(),
            $parsedBody,
            $psrRequest->getAttributes(),
            $psrRequest->getCookieParams(),
            [], // $this->getFiles($psrRequest->getUploadedFiles()), // Note: We don't need files for authentication middleware
            $server,
            $streamed ? $psrRequest->getBody()->detach() : $psrRequest->getBody()->__toString()
        );
        $request->headers->add($psrRequest->getHeaders());
        return $request;
    }
}
