<?php

namespace PHPMaker2025\ucarsip;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Abstract controller class
 */
abstract class AbstractController
{
    protected Language $language;
    protected AdvancedSecurity $security;
    protected SessionInterface $session;

    /**
     * Constructor
     */
    public function __construct(protected ContainerInterface $container)
    {
        $this->language = $container->get('app.language');
        $this->security = $container->get('app.security');
        $this->session = $container->get('app.session');
    }

    /**
     * Checks if the attribute is granted against the current authentication token and optionally supplied subject
     * e.g. $hasAccess = $this->isGranted('ROLE_ADMIN');
     *
     * @throws LogicException
     */
    protected function isGranted(mixed $attribute, mixed $subject = null): bool
    {
        return $this->security->isGranted($attribute);
    }

    /**
     * Throw an exception unless the attribute is granted against the current authentication token and optionally supplied subject
     * e.g. $this->denyAccessUnlessGranted('ROLE_ADMIN');
     *
     * @throws AccessDeniedException
     */
    protected function denyAccessUnlessGranted(mixed $attribute, mixed $subject = null, ?string $message = null): void
    {
        if (!$this->isGranted($attribute, $subject)) {
            $exception = new AccessDeniedException($message ?? $this->language->phrase('403Desc'));
            $exception->setAttributes([$attribute]);
            $exception->setSubject($subject);
            throw $exception;
        }
    }
}
