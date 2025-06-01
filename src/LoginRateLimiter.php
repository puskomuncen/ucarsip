<?php

namespace PHPMaker2025\ucarsip;

use Symfony\Component\HttpFoundation\RateLimiter\AbstractRequestRateLimiter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;
use Symfony\Component\Security\Http\SecurityRequestAttributes;

/**
 * Login throttling limiter
 */
class LoginRateLimiter extends AbstractRequestRateLimiter
{
    private RateLimiterFactory $globalFactory;
    private RateLimiterFactory $localFactory;

    public function __construct(RateLimiterFactory $globalFactory, RateLimiterFactory $localFactory)
    {
        $this->globalFactory = $globalFactory;
        $this->localFactory = $localFactory;
    }

    protected function getLimiters(Request $request): array
    {
        $username = $request->attributes->get(SecurityRequestAttributes::LAST_USERNAME, '');
        $username = preg_match('//u', $username) ? mb_strtolower($username, 'UTF-8') : strtolower($username);
        $clientIp = $request->getClientIp();
        return [
            $this->globalFactory->create($username), // Username
            $this->globalFactory->create($clientIp), // IP
            $this->localFactory->create($username . '-' . $clientIp), // Username + IP
        ];
    }
}
