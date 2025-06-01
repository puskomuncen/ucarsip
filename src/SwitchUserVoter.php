<?php

namespace PHPMaker2025\ucarsip;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class SwitchUserVoter extends Voter
{

    protected function supports(string $attribute, mixed $subject): bool
    {
        // If the attribute is not that we support, return false
        if ($attribute != Config('SECURITY.firewalls.main.switch_user.role')) {
            return false;
        }

        // Only vote on database user
        if (!IsEntityUser($subject)) {
            return false;
        }
        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        if (IsAdmin()) {
            return true;
        }

        // Current user must be logged in so the user IDs are loaded
        if (!IsLoggedIn() || !Config("USER_ID_FIELD_NAME")) {
            return false;
        }

        // Current user
        $user = $token->getUser();

        // New user to be switched to
        $newUser = $subject;

        // The two users should not be the same user
        if ($user->getUserIdentifier() ==  $newUser->getUserIdentifier()) {
            return false;
        }

        // Make sure the current user is a parent user of the new user
        return in_array($newUser->get(Config("USER_ID_FIELD_NAME")), Security()->UserIDs);
    }
}
