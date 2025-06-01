<?php

namespace PHPMaker2025\ucarsip;

use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Exception;

/**
 * URL Generator (for login link only)
 */
class UrlGenerator implements UrlGeneratorInterface, UrlMatcherInterface
{
    protected RequestContext $context;

    // For UrlGeneratorInterface (not used)
    public function setContext(RequestContext $context): void
    {
        $this->context = $context;
    }

    // For UrlGeneratorInterface (not used)
    public function getContext(): RequestContext
    {
        return $this->context;
    }

    /**
     * Match path info (for login link)
     *
     * @param string $pathinfo Current path info
     * @return array Returns route name as login link's "check_route" to enable the authenticator
     */
    public function match(string $pathinfo): array
    {
        if ($pathinfo == '/login_check') { // Check current request URL
            return ['_route' => 'login_check'];
        }
        return [];
    }

    /**
     * Generates a URL or path for a specific route based on the given parameters
     * Note: Since we only use this to generate login link without route parameters, we output all parameters as query string.
     *
     * @param string $name Route name
     * @param array $parameters Parameters (route data not supported)
     * @param int $referenceType URL type
     * @return string URL
     */
    public function generate(string $name, array $parameters = [], int $referenceType = self::ABSOLUTE_URL): string
    {
        if (!$name || !RouteCollector()->getNamedRoute($name)) {
            throw new RouteNotFoundException(sprintf('Unable to generate a URL for the named route "%s" as such route does not exist.', $name));
        }
        if ($referenceType == self::ABSOLUTE_URL) {
            return FullUrlFor($name, [], $parameters);
        } elseif ($referenceType == self::ABSOLUTE_PATH) {
            return UrlFor($name, [], $parameters);
        } elseif ($referenceType == self::RELATIVE_PATH) {
            return RelativeUrlFor($name, [], $parameters);
        }
        throw new Exception(sprintf('Unable to generate a URL for the named route "%s" as the URL type "%s" is not supported.', $name, $referenceType));
    }
}
