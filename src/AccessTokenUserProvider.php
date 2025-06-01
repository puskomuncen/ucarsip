<?php

namespace PHPMaker2025\ucarsip;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use ReflectionClass;

class AccessTokenUserProvider implements UserProviderInterface
{
    protected ?string $provider = null;

    /**
     * Load user by identifier
     *
     * @throws UserNotFoundException if the user is not found
     */
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $adapters = Container("hybridauth")->getConnectedAdapters();
        foreach ($adapters as $adapter) {
            $provider = $adapter->providerName ?? (new ReflectionClass($adapter))->getShortName();
            if ($this->provider === null || $this->provider == $provider) {
                $profile = $adapter->getUserProfile(); // Hybridauth\User\Profile
                $claims = (array)$profile;
                $claims["userIdentifier"] = $claims[$config["identifyingAttribute"] ?? "email"];
                if ($claims["userIdentifier"] == $identifier) {
                    $claims["provider"] = $provider;
                    return new AccessTokenUser(...$claims);
                }
            }
        }
        $ex = new UserNotFoundException(sprintf('There is no user with identifier "%s".', $identifier));
        $ex->setUserIdentifier($identifier);
        throw $ex;
    }

    /**
     * Refresh the user after being reloaded from the session.
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof AccessTokenUser) {
            throw new UnsupportedUserException(sprintf('Invalid user class "%s".', get_class($user)));
        }

        // Return a User object after making sure its data is "fresh"
        $this->provider = $user->provider;
        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    /**
     * Tell Symfony to use this provider for this User class.
     */
    public function supportsClass(string $class): bool
    {
        return AccessTokenUser::class === $class;
    }
}
