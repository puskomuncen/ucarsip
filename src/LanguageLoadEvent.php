<?php

namespace PHPMaker2025\ucarsip;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Language Load Event
 */
class LanguageLoadEvent extends Event
{
    public const NAME = "language.load";

    public function __construct(protected Language $language)
    {
    }

    public function getLanguage(): Language
    {
        return $this->language;
    }

    public function getSubject(): Language
    {
        return $this->language;
    }

    public function setPhrase(string $id, string $value): void
    {
        $this->language->setPhrase($id, $value);
    }
}
