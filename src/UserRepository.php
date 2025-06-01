<?php

namespace PHPMaker2025\ucarsip;

use PHPMaker2025\ucarsip\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

class UserRepository extends EntityRepository implements UserLoaderInterface
{
    /**
     * Loads the user for the given user identifier (e.g. username or email).
     *
     * This method must throw UserNotFoundException if the user is not found.
     *
     * @return UserInterface
     *
     * @throws UserNotFoundException
     */
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = parent::findOneBy([
            "Username" => $identifier
        ]);
        if (null === $user) {
            $e = new UserNotFoundException(sprintf('User "%s" not found.', $identifier));
            $e->setUserIdentifier($identifier);
            throw $e;
        }
        return $user;
    }
}
