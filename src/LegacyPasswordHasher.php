<?php

namespace PHPMaker2025\ucarsip;

use Symfony\Component\PasswordHasher\Exception\InvalidPasswordException;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

class LegacyPasswordHasher implements PasswordHasherInterface
{

    public function hash(string $plainPassword): string
    {
        return Config("ENCRYPTED_PASSWORD")
            ? EncryptPassword(Config("CASE_SENSITIVE_PASSWORD") ? $plainPassword : strtolower($plainPassword))
            : $plainPassword;
    }

    public function verify(string $hashedPassword, string $plainPassword): bool
    {
        if (trim($plainPassword) === "") {
            return false;
        }
        return ComparePassword($hashedPassword, $plainPassword);
    }

    public function needsRehash(string $hashedPassword): bool
    {
        return Config("MIGRATE_PASSWORD");
    }
}
