<?php

namespace PHPMaker2025\ucarsip;

use Symfony\Component\Ldap\Entry;
use Symfony\Component\Ldap\Exception\ExceptionInterface;
use Symfony\Component\Ldap\Exception\InvalidCredentialsException;
use Symfony\Component\Ldap\Exception\InvalidSearchCredentialsException;
use Symfony\Component\Ldap\LdapInterface;
use Symfony\Component\Ldap\Security\LdapUser;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Exception;

/**
 * Custom LDAP User Provider
 *
 * Based on Symfony\Component\Ldap\Security\LdapUserProvider
 */
class CustomLdapUserProvider implements UserProviderInterface, PasswordUpgraderInterface
{
    private string $uidKey;
    private string $defaultSearch;

    public function __construct(
        private LdapInterface $ldap,
        private string $baseDn,
        private ?string $searchDn = null,
        #[\SensitiveParameter] private ?string $searchPassword = null,
        private array $defaultRoles = [],
        ?string $uidKey = null,
        ?string $filter = null,
        private ?string $passwordAttribute = null,
        private array $extraFields = [],
        private ?RequestStack $requestStack = null, //***
    ) {
        $uidKey ??= 'sAMAccountName';
        $filter ??= '({uid_key}={user_identifier})';
        $this->uidKey = $uidKey;
        $this->defaultSearch = str_replace('{uid_key}', $uidKey, $filter);
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $dn = str_replace('{user_identifier}', $identifier, Config('SECURITY.firewalls.main.form_login_ldap.dn_string'));
        try {
            if ($this->searchDn && $this->searchPassword) { // Use search_dn and search_password if provided
                $this->ldap->bind($this->searchDn, $this->searchPassword);
            } elseif (
                ($request = $this->requestStack?->getCurrentRequest())
                && ($username = $request->get(Config('SECURITY.firewalls.main.form_login_ldap.username_parameter')))
                && ($password = $request->get(Config('SECURITY.firewalls.main.form_login_ldap.password_parameter')))
                && $username == $identifier
            ) { // Otherwise check if username and password are present in the request
                $this->ldap->bind($dn, $password);
            } else {
                $this->ldap->bind(null, null);
            }
        } catch (InvalidCredentialsException) {
            throw new InvalidSearchCredentialsException();
        }
        try {
            $identifier = $this->ldap->escape($identifier, '', LdapInterface::ESCAPE_FILTER);
            $query = str_replace('{user_identifier}', $identifier, $this->defaultSearch);
            $search = $this->ldap->query($this->baseDn, $query, ['filter' => 0 == \count($this->extraFields) ? '*' : $this->extraFields]);
            $entries = $search->execute();
            $count = \count($entries);
            if (!$count) {
                $e = new UserNotFoundException(\sprintf('User "%s" not found.', $identifier));
                $e->setUserIdentifier($identifier);
                throw $e;
            }
            if ($count > 1) {
                $e = new UserNotFoundException('More than one user found.');
                $e->setUserIdentifier($identifier);
                throw $e;
            }
            $entry = $entries[0];
            try {
                $identifier = $this->getAttributeValue($entry, $this->uidKey);
            } catch (InvalidArgumentException) {
            }
            return $this->loadUser($identifier, $entry);
        } catch (Exception) {
            new LdapUser(new Entry($dn), $identifier, null, $this->defaultRoles);
        }
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof LdapUser) {
            throw new UnsupportedUserException(\sprintf('Instances of "%s" are not supported.', get_debug_type($user)));
        }
        return new LdapUser($user->getEntry(), $user->getUserIdentifier(), $user->getPassword(), $user->getRoles(), $user->getExtraFields());
    }

    /**
     * @final
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof LdapUser) {
            throw new UnsupportedUserException(\sprintf('Instances of "%s" are not supported.', get_debug_type($user)));
        }
        if (null === $this->passwordAttribute) {
            return;
        }
        try {
            $user->getEntry()->setAttribute($this->passwordAttribute, [$newHashedPassword]);
            $this->ldap->getEntryManager()->update($user->getEntry());
            $user->setPassword($newHashedPassword);
        } catch (ExceptionInterface) {
            // ignore failed password upgrades
        }
    }

    public function supportsClass(string $class): bool
    {
        return LdapUser::class === $class;
    }

    /**
     * Loads a user from an LDAP entry.
     */
    protected function loadUser(string $identifier, Entry $entry): UserInterface
    {
        $password = null;
        $extraFields = [];
        if (null !== $this->passwordAttribute) {
            $password = $this->getAttributeValue($entry, $this->passwordAttribute);
        }
        foreach ($this->extraFields as $field) {
            $extraFields[$field] = $this->getAttributeValue($entry, $field);
        }
        return new LdapUser($entry, $identifier, $password, $this->defaultRoles, $extraFields);
    }

    private function getAttributeValue(Entry $entry, string $attribute): mixed
    {
        if (!$entry->hasAttribute($attribute)) {
            throw new InvalidArgumentException(\sprintf('Missing attribute "%s" for user "%s".', $attribute, $entry->getDn()));
        }
        $values = $entry->getAttribute($attribute);
        if (!\in_array($attribute, [$this->uidKey, $this->passwordAttribute])) {
            return $values;
        }
        if (1 !== \count($values)) {
            throw new InvalidArgumentException(\sprintf('Attribute "%s" has multiple values.', $attribute));
        }
        return $values[0];
    }
}
