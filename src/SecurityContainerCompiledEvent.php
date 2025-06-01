<?php

namespace PHPMaker2025\ucarsip;

use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Security Container Compiled Event
 */
class SecurityContainerCompiledEvent extends Event
{
    public const NAME = "security.container.compiled";

    public function __construct(protected ContainerBuilder $builder)
    {
    }

    public function getBuilder(): ContainerBuilder
    {
        return $this->builder;
    }

    public function getSubject(): ContainerBuilder
    {
        return $this->builder;
    }
}
