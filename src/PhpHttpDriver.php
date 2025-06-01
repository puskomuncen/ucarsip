<?php

namespace PHPMaker2025\ucarsip;

use DebugBar\HttpDriverInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * HTTP driver for debug bar
 * Based on DebugBar\PhpHttpDriver
 */
class PhpHttpDriver implements HttpDriverInterface
{

    public function __construct(protected SessionInterface $session)
    {
    }

    /**
     * @param array $headers
     */
    function setHeaders(array $headers)
    {
        foreach ($headers as $name => $value) {
            header("$name: $value");
        }
    }

    /**
     * @return bool
     */
    function isSessionStarted()
    {
        return $this->session->isStarted();
    }

    /**
     * @param string $name
     * @param string $value
     */
    function setSessionValue($name, $value)
    {
        $this->session->set($name, $value);
    }

    /**
     * @param string $name
     * @return bool
     */
    function hasSessionValue($name)
    {
        return $this->session->has($name);
    }

    /**
     * @param string $name
     * @return mixed
     */
    function getSessionValue($name)
    {
        return $this->session->get($name);
    }

    /**
     * @param string $name
     */
    function deleteSessionValue($name)
    {
        $this->session->remove($name);
    }
}
