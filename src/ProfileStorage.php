<?php

namespace PHPMaker2025\ucarsip;

use Symfony\Component\RateLimiter\LimiterStateInterface;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\RateLimiter\Storage\StorageInterface;

/**
 * User profile storage for rate limiter
 */
class ProfileStorage implements StorageInterface
{

    public function __construct(
        private UserProfile $profile,
        private AuthenticationUtils $utils
    ) {
        if ($username = $utils->getLastUsername()) {
            $profile->setUserName($utils->getLastUsername())->loadFromStorage();
        }
    }

    public function save(LimiterStateInterface $limiterState): void
    {
        $this->profile->set($limiterState->getId(), [$this->getExpireAt($limiterState), base64_encode(serialize($limiterState))])->saveToStorage();
    }

    public function fetch(string $limiterStateId): ?LimiterStateInterface
    {
        if (!$this->profile->has($limiterStateId)) {
            return null;
        }
        [$expireAt, $limiterState] = $this->profile->get($limiterStateId);
        if (null !== $expireAt && $expireAt <= microtime(true)) {
            $this->profile->delete($limiterStateId)->saveToStorage();
            return null;
        }
        return unserialize(base64_decode($limiterState));
    }

    public function delete(string $limiterStateId): void
    {
        if (!$this->profile->has($limiterStateId)) {
            return;
        }
        $this->profile->delete($limiterStateId)->saveToStorage();
    }

    private function getExpireAt(LimiterStateInterface $limiterState): ?float
    {
        if (null !== $expireSeconds = $limiterState->getExpirationTime()) {
            return microtime(true) + $expireSeconds;
        }
        return $this->profile->get($limiterState->getId())[0] ?? null;
    }
}
