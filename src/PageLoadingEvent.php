<?php

namespace PHPMaker2025\ucarsip;

use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Page Loading Event
 */
class PageLoadingEvent extends GenericEvent
{
    public const NAME = "page.loading";

    public function getPage(): mixed
    {
        return $this->subject;
    }
}
