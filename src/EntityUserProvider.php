<?php

namespace PHPMaker2025\ucarsip;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Entity User Provider
 */
class EntityUserProvider implements UserProviderInterface, PasswordUpgraderInterface
{

    public function __construct(private readonly string $class)
    {
    }

    /**
     * Load user by identifier
     *
     * @throws UserNotFoundException if the user is not found
     */
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = LoadUserByIdentifier($identifier);
        if (null === $user) {
            $e = new UserNotFoundException(sprintf('User "%s" not found.', $identifier));
            $e->setUserIdentifier($identifier);
            throw $e;
        }
        return $user;
    }

    /**
     * Refresh the user after being reloaded from the session
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        $class = $this->getClass();
        if (!$user instanceof $class) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_debug_type($user)));
        }

        // The user must be reloaded via the primary key as all other data
        // might have changed without proper persistence in the database.
        // That's the case when the user has been changed by a form with
        // validation errors.
        if (!$id = $user->getUserIdentifier()) {
            throw new \InvalidArgumentException('You cannot refresh a user from the EntityUserProvider that does not contain an identifier. The user object has to be serialized with its own identifier mapped by Doctrine.');
        }
        $refreshedUser = LoadUserByIdentifier($id);
        if (null === $refreshedUser) {
            $e = new UserNotFoundException('User with id ' . json_encode($id) . ' not found.');
            $e->setUserIdentifier(json_encode($id));
            throw $e;
        }
        return $refreshedUser;
    }

    /**
     * Tell Symfony to use this provider for this User class
     */
    public function supportsClass(string $class): bool
    {
        return $class === $this->getClass() || is_subclass_of($class, $this->getClass());
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        $class = $this->getClass();
        if (!$user instanceof $class) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_debug_type($user)));
        }
        $repository = UserRepository();
        if ($user instanceof PasswordAuthenticatedUserInterface && $repository instanceof PasswordUpgraderInterface) {
            $repository->upgradePassword($user, $newHashedPassword);
            $user->set_Password($newHashedPassword);
        }
    }

    private function getClass(): string
    {
        return $this->class;
    }
}
