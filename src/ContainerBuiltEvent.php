<?php

namespace PHPMaker2025\ucarsip;

use Symfony\Contracts\EventDispatcher\Event;
use Psr\Container\ContainerInterface;

/**
 * Container Built Event
 */
class ContainerBuiltEvent extends Event
{
    public const NAME = "container.built";

    public function __construct(protected ContainerInterface $container)
    {
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    public function getSubject(): ContainerInterface
    {
        return $this->container;
    }
}
