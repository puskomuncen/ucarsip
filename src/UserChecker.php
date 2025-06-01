<?php

namespace PHPMaker2025\ucarsip;

use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    // Triggered by CheckPassportEvent
    public function checkPreAuth(UserInterface $user): void
    {
        if (!IsEntityUser($user)) {
            return;
        }

        // The message passed to this exception is meant to be displayed to the user
        if (Config("REGISTER_ACTIVATE") && Config("USER_ACTIVATED_FIELD_NAME") && !ConvertToBool($user->get(Config("USER_ACTIVATED_FIELD_NAME"))) && RouteName() != "login_check") {
            $ex = new CustomUserMessageAccountStatusException(sprintf(Language()->phrase("ActivatePending"), $user->getUserIdentifier()));
            $ex->setUser($user);
            throw $ex;
        }
    }

    // Triggered by AuthenticationSuccessEvent
    public function checkPostAuth(UserInterface $user): void
    {
        if (!IsEntityUser($user)) {
            return;
        }
    }
}
