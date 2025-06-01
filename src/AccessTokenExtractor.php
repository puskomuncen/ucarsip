<?php

namespace PHPMaker2025\ucarsip;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\AccessToken\AccessTokenExtractorInterface;

/**
 * Access token extractor
 */
class AccessTokenExtractor implements AccessTokenExtractorInterface
{

    public function extractAccessToken(Request $request): ?string
    {
        // Return the provider name as access token for AccessTokenHandler to handle
        return RouteName() == "login"
            && ($provider = Route("action"))
            && Config("AUTH_CONFIG.providers." . $provider . ".enabled")
                ? $provider
                : null;
    }
}
