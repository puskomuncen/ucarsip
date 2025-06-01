<?php

namespace PHPMaker2025\ucarsip;

use Symfony\Component\Security\Core\Authentication\RememberMe\PersistentToken;
use Symfony\Component\Security\Core\Authentication\RememberMe\PersistentTokenInterface;
use Symfony\Component\Security\Core\Authentication\RememberMe\TokenProviderInterface;
use Symfony\Component\Security\Core\Authentication\RememberMe\TokenVerifierInterface;
use Symfony\Component\Security\Core\Authentication\RememberMe\CacheTokenVerifier;
use Symfony\Component\Security\Core\Exception\TokenNotFoundException;
use Psr\Cache\CacheItemPoolInterface;

/**
 * This class provides storage for the tokens that is set in "remember-me"
 * cookies. This way no password secrets will be stored in the cookies on
 * the client machine, and thus the security is improved.
 */
class RememberMeTokenProvider implements TokenProviderInterface, TokenVerifierInterface
{
    /**
     * @param int $outdatedTokenTtl How long the outdated token should still be considered valid. Defaults
     *                              to 60, which matches how often the PersistentRememberMeHandler will at
     *                              most refresh tokens. Increasing to more than that is not recommended,
     *                              but you may use a lower value.
     */
    public function __construct(
        protected CacheItemPoolInterface $cache,
        protected int $outdatedTokenTtl = 60,
        protected string $cacheKeyPrefix = 'rememberme-',
        protected string $stalePrefix = 'stale-',
    ) {
    }

    protected function getCacheKey(string $series): string
    {
        return $this->cacheKeyPrefix . rawurlencode($series);
    }

    public function loadTokenBySeries(string $series): PersistentTokenInterface
    {
        $item = $this->cache->getItem($this->getCacheKey($series));
        if (!$item->isHit()) {
            throw new TokenNotFoundException('No token found.');
        }
        $token = json_decode($item->get(), true);
        return new PersistentToken($token['class'], $token['userIdentifier'], $series, $token['tokenValue'], new \DateTimeImmutable($token['lastUsed']));
    }

    public function deleteTokenBySeries(string $series): void
    {
        $this->cache->deleteItem($this->getCacheKey($series));
    }

    public function updateToken(string $series, #[\SensitiveParameter] string $tokenValue, \DateTimeInterface $lastUsed): void
    {
        $item = $this->cache->getItem($this->getCacheKey($series));
        if (!$item->isHit()) {
            throw new TokenNotFoundException('No token found.');
        }
        $token = json_decode($item->get(), true);
        $token['tokenValue'] = $tokenValue;
        $token['lastUsed'] = $lastUsed->format('c');
        $item->set(json_encode($token));
        $this->cache->save($item);
    }

    public function createNewToken(PersistentTokenInterface $token): void
    {
        $item = $this->cache->getItem($this->getCacheKey($token->getSeries()));
        $token = [
            'class' => $token->getClass(),
            'userIdentifier' => $token->getUserIdentifier(),
            'series' => $token->getSeries(),
            'tokenValue'=> $token->getTokenValue(),
            'lastUsed' => $token->getLastUsed()->format('c')
        ];
        $item->set(json_encode($token));
        if (str_starts_with($token['series'], $this->stalePrefix)) // Temp series
            $item->expiresAfter($this->outdatedTokenTtl);
        $this->cache->save($item);
    }

    public function verifyToken(PersistentTokenInterface $token, #[\SensitiveParameter] string $tokenValue): bool
    {
        // Check if the token value matches the current persisted token
        if (hash_equals($token->getTokenValue(), $tokenValue)) {
            return true;
        }

        // Get the series id of previous token
        $tmpSeries = $this->stalePrefix . $token->getSeries();

        // Check if the previous token is present. If the given $tokenValue
        // matches the previous token (and it is outdated by at most 60seconds)
        // we also accept it as a valid value.
        try {
            $tmpToken = $this->loadTokenBySeries($tmpSeries);
        } catch (TokenNotFoundException) {
            return false;
        }
        if ($tmpToken->getLastUsed()->getTimestamp() + $this->outdatedTokenTtl < time()) {
            return false;
        }
        return hash_equals($tmpToken->getTokenValue(), $tokenValue);
    }

    public function updateExistingToken(PersistentTokenInterface $token, #[\SensitiveParameter] string $tokenValue, \DateTimeInterface $lastUsed): void
    {
        if (!$token instanceof PersistentToken) {
            return;
        }

        // Persist a copy of the previous token for authentication
        // in verifyToken should the old token still be sent by the browser
        // in a request concurrent to the one that did this token update
        $tmpSeries = $this->stalePrefix . $token->getSeries();
        try {
            $this->deleteTokenBySeries($tmpSeries);
            $lastUsed = \DateTime::createFromInterface($lastUsed);
            $this->createNewToken(new PersistentToken($token->getClass(), $token->getUserIdentifier(), $tmpSeries, $token->getTokenValue(), $lastUsed));
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
