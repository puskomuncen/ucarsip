<?php

namespace PHPMaker2025\ucarsip;

use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\FallbackUserLoader;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Exception;

class AccessTokenHandler implements AccessTokenHandlerInterface
{

    public function getUserBadgeFrom(string $accessToken): UserBadge
    {
        $provider = $accessToken; // Note: $accessToken is only the provider name
        try {
            $hybridauth = Container("hybridauth");
            $adapter = $hybridauth->authenticate($provider); // Authenticate with the selected provider
            if ($adapter->isConnected()) {
                $profile = $adapter->getUserProfile(); // Hybridauth\User\Profile
                $config = $hybridauth->getProviderConfig($provider);
                $identifier = $config["userIdentifier"] ?? "email";
                // Return a UserBadge object containing the user identifier
                return new UserBadge($profile->$identifier);
            }
        } catch (Throwable $e) {
            throw new BadCredentialsException($e->getMessage());
        }
    }
}
