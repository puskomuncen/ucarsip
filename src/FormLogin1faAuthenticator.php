<?php

namespace PHPMaker2025\ucarsip;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\ParameterBagUtils;
use Symfony\Component\Security\Http\SecurityRequestAttributes;

class FormLogin1faAuthenticator extends AbstractLoginFormAuthenticator
{

    public function __construct(
        protected UserProviderInterface $userProvider,
        protected AuthenticationSuccessHandlerInterface $successHandler,
        protected AuthenticationFailureHandlerInterface $failureHandler,
        protected Language $language,
    ) {
    }

    protected function getLoginUrl(Request $request): string
    {
        return UrlFor('login1fa');
    }

    public function authenticate(Request $request): Passport
    {
        $credentials = $this->getCredentials($request);
        if (IsEmpty($credentials['username']) || IsEmpty($credentials['password']) && !Config('OTP_ONLY')) { // Check empty username / password
            throw new CustomUserMessageAuthenticationException($this->language->phrase("InvalidUidPwd"));
        }
        if (Config("USE_PHPCAPTCHA_FOR_LOGIN")) { // Validate captcha for login
            $captcha = Captcha();
            $captcha->Response = Post($captcha->getElementName());
            $sessionName = AddTabId($captcha->getSessionName("login"));
            if ($captcha->Response != $request->getSession()->get($sessionName)) {
                throw new CustomUserMessageAuthenticationException($this->language->phrase(IsEmpty($captcha->Response) ? "EnterValidateCode" : "IncorrectValidationCode"));
            }
        }
        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $credentials['username']);
        $userBadge = new UserBadge(
            $credentials['username'],
            $this->userProvider->loadUserByIdentifier(...)
        );
        $passport = Config('OTP_ONLY') // Check user name only
            ? new Passport($userBadge, new CustomCredentials(fn($credentials, $user) => $user != null, null))
            : new Passport($userBadge, new PasswordCredentials($credentials['password']));
        $passport->setAttribute('_remember_me', $credentials['_remember_me']);
        $profile = Profile()->setUserName($credentials['username'])->loadFromStorage();
        if (!$profile->get2FAEnabled()) {
            $passport->addBadge(new RememberMeBadge($passport->getAttributes()));
        }
        return $passport;
    }

    protected function getCredentials(Request $request): array
    {
        $credentials = [
            'username' => ParameterBagUtils::getParameterBagValue($request->request, 'username'),
            'password' => ParameterBagUtils::getParameterBagValue($request->request, 'password') ?? '',
            '_remember_me' => ParameterBagUtils::getParameterBagValue($request->request, Config('SECURITY.firewalls.main.remember_me.remember_me_parameter'))
        ];
        return $credentials;
    }

    protected function useTwoFactorAuthentication(UserInterface $user): bool
    {
        return Profile()->setUser($user)->get2FAEnabled();
    }

    public function createToken(Passport $passport, string $firewallName): TokenInterface
    {
        $user = $passport->getUser();
        if ($this->useTwoFactorAuthentication($user)) {
            $token = new TwoFactorAuthenticatingToken($user, $firewallName, $user->getRoles());
            $token->setAttribute(Config('SECURITY.firewalls.main.remember_me.remember_me_parameter'), $passport->getAttribute('_remember_me'));
            return $token;
        } else {
            return new UsernamePasswordToken($user, $firewallName, $user->getRoles());
        }
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($this->useTwoFactorAuthentication($token->getUser())) {
            $request->getSession()->set(SESSION_STATUS, 'loggingin2fa');
            return new JsonResponse(['url' => UrlFor('login2fa')]); // Go to 2nd factor authentication
        } else {
            return $this->successHandler->onAuthenticationSuccess($request, $token);
        }
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        return $this->failureHandler->onAuthenticationFailure($request, $exception);
    }
}
