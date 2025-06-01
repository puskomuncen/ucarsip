<?php

namespace PHPMaker2025\ucarsip;

use ArrayObject;

/**
 * ListOptions collection
 */
class ListOptionsCollection extends ArrayObject
{
    // Constructor
    public function __construct(array $array = [])
    {
        parent::__construct($array, ArrayObject::ARRAY_AS_PROPS);
    }

    // Render
    public function render(string $part, string $pos = ""): void
    {
        foreach ($this as $options) {
            $options->render($part, $pos);
        }
    }

    // Hide all options
    public function hideAllOptions(): void
    {
        foreach ($this as $options) {
            $options->hideAllOptions();
        }
    }

    // Visible
    public function visible(): bool
    {
        return array_any($this->getArrayCopy(), fn($options) => $options->visible());
    }
}
