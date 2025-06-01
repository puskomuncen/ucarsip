<?php

namespace PHPMaker2025\ucarsip;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\ParameterBagUtils;

class FormLogin2faAuthenticator extends AbstractLoginFormAuthenticator
{

    public function __construct(
        protected UserProviderInterface $userProvider,
        protected TokenStorageInterface $tokenStorage,
        protected AuthenticationSuccessHandlerInterface $successHandler,
        protected AuthenticationFailureHandlerInterface $failureHandler,
        protected Language $language,
    ) {
    }

    protected function getLoginUrl(Request $request): string
    {
        return UrlFor('login2fa');
    }

    public function supports(Request $request): bool
    {
        return $request->isMethod('POST')
            && $this->getLoginUrl($request) === $request->getBaseUrl(). $request->getPathInfo()
            && 'form' === $request->getContentTypeFormat()
            && $this->tokenStorage->getToken() instanceof TwofactorAuthenticatingToken
            && ParameterBagUtils::getParameterBagValue($request->request, 'securitycode')
            && ParameterBagUtils::getParameterBagValue($request->request, 'action') != 'reset';
    }

    public function authenticate(Request $request): Passport
    {
        $credentials = $this->getCredentials($request);
        $user = $this->tokenStorage->getToken()->getUser();
        $userBadge = new UserBadge($user->getUserIdentifier(), fn($identifier) => $user);
        $checker = fn($credentials, $user) => Profile()->setUser($user)->verify2FACode($credentials['securitycode'], $credentials['authtype']);
        return new Passport($userBadge, new CustomCredentials($checker, $credentials), [new RememberMeBadge($this->tokenStorage->getToken()->getAttributes())]);
    }

    protected function getCredentials(Request $request): array
    {
        $session = $request->getSession();
        $credentials = [
            'authtype' => ParameterBagUtils::getParameterBagValue($request->request, 'authtype'),
            'securitycode' => ParameterBagUtils::getParameterBagValue($request->request, 'securitycode')
        ];
        if (!is_string($credentials['authtype']) && !$credentials['authtype'] instanceof \Stringable) {
            throw new BadRequestHttpException(sprintf('The key "%s" must be a string, "%s" given.', 'authtype', gettype($credentials['authtype'])));
        }
        if (!is_string($credentials['securitycode']) && !$credentials['securitycode'] instanceof \Stringable) {
            throw new BadRequestHttpException(sprintf('The key "%s" must be a string, "%s" given.', 'securitycode', gettype($credentials['securitycode'])));
        }
        return $credentials;
    }

    public function createToken(Passport $passport, string $firewallName): TokenInterface
    {
        return new TwoFactorAuthenticatedToken($passport->getUser(), $firewallName, $passport->getUser()->getRoles());
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $request->getSession()->set(SESSION_STATUS, "loggingin");
        return new JsonResponse(["success" => true]);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        return new JsonResponse(["success" => false, "error" => $this->language->phrase("IncorrectSecurityCode")]);
    }
}
