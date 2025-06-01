<?php

namespace PHPMaker2025\ucarsip;

use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;

/**
 * Services Configuration Event
 */
class ServicesConfigurationEvent extends Event
{
    public const NAME = "services.configuration";

    public function __construct(protected ServicesConfigurator $services)
    {
    }

    public function getServices(): ServicesConfigurator
    {
        return $this->services;
    }

    public function getSubject(): ServicesConfigurator
    {
        return $this->services;
    }
}
