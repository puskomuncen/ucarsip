<?php

namespace PHPMaker2025\ucarsip;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Exception\RequestExceptionInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\HttpKernel as BaseHttpKernel;
use Throwable;

/**
 * HttpKernel for Symfony security middleware
 */
class HttpKernel extends BaseHttpKernel
{

    public function handle(Request $request, int $type = HttpKernelInterface::MAIN_REQUEST, bool $catch = true): Response
    {
        $request->headers->set('X-Php-Ob-Level', (string) ob_get_level());
        $this->requestStack->push($request);
        try {
            $event = new RequestEvent($this, $request, $type);
            $this->dispatcher->dispatch($event, KernelEvents::REQUEST);
            return $event->hasResponse()
                ? $this->filterResponse($event->getResponse(), $request, $type)
                : new Response(); // Empty response, we only handle redirection and cookies
        } catch (Throwable $e) {
            if ($e instanceof \Error) {
                throw $e;
            }
            if ($e instanceof RequestExceptionInterface) {
                $e = new BadRequestHttpException($e->getMessage(), $e);
            }
            if (false === $catch) {
                $this->finishRequest($request, $type);
                throw $e;
            }
            return $this->handleThrowable($e, $request, $type);
        }
    }

    /**
     * Filters a response object.
     *
     * @throws \RuntimeException if the passed object is not a Response instance
     */
    private function filterResponse(Response $response, Request $request, int $type): Response
    {
        $event = new ResponseEvent($this, $request, $type, $response);
        $this->dispatcher->dispatch($event, KernelEvents::RESPONSE);
        $this->finishRequest($request, $type);
        return $event->getResponse();
    }

    /**
     * Publishes the finish request event, then pop the request from the stack.
     *
     * Note that the order of the operations is important here, otherwise
     * operations such as {@link RequestStack::getParentRequest()} can lead to
     * weird results.
     */
    private function finishRequest(Request $request, int $type): void
    {
        $this->dispatcher->dispatch(new FinishRequestEvent($this, $request, $type), KernelEvents::FINISH_REQUEST);
    }
}
