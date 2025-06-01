<?php

namespace PHPMaker2025\ucarsip;

/**
 * Form trait
 */
trait FormTrait
{
    protected int $FormIndex = -1;

    public function getFormRowActionName(bool $indexed = false)
    {
        $name = match ($this->PageID) {
            "list" => Config("FORM_ROW_ACTION_NAME"),
            "grid" => Config("FORM_ROW_ACTION_NAME") . "_" . $this->FormName,
            default => ""
        };
        return $indexed ? $this->getFormIndexedName($name) : $name;
    }

    public function getFormBlankRowName(bool $indexed = false)
    {
        $name = match ($this->PageID) {
            "list" => Config("FORM_BLANK_ROW_NAME"),
            "grid" => Config("FORM_BLANK_ROW_NAME") . "_" . $this->FormName,
            default => ""
        };
        return $indexed ? $this->getFormIndexedName($name) : $name;
    }

    public function getFormOldKeyName(bool $indexed = false)
    {
        $name = match ($this->PageID) {
            "grid" => Config("FORM_OLD_KEY_NAME") . "_" . $this->FormName,
            default => Config("FORM_OLD_KEY_NAME")
        };
        return $indexed ? $this->getFormIndexedName($name) : $name;
    }

    public function getFormRowHashName(bool $indexed = false)
    {
        $name = match ($this->PageID) {
            "grid" => Config("FORM_ROW_HASH_NAME") . "_" . $this->FormName,
            default => Config("FORM_ROW_HASH_NAME")
        };
        return $indexed ? $this->getFormIndexedName($name) : $name;
    }

    public function getFormKeyCountName()
    {
        return match ($this->PageID) {
            "list" => Config("FORM_KEY_COUNT_NAME"),
            "grid" => Config("FORM_KEY_COUNT_NAME") . "_" . $this->FormName,
            default => ""
        };
    }

    public function getFormIndexedName(string $name): string
    {
        return preg_match(Config("FORM_HIDDEN_INPUT_NAME_PATTERN"), $name) && $this->FormIndex >= 0
            ? substr($name, 0, 1) . $this->FormIndex . substr($name, 1)
            : $name;
    }

    public function isGridPage(): bool
    {
        return $this->PageID == "grid";
    }

    public function hasFormValue(string $name): bool
    {
        $wrkname = $this->getFormIndexedName($name);
        if ($this->isGridPage() && preg_match(Config("FORM_HIDDEN_INPUT_NAME_PATTERN"), $name)) {
            if (Post($this->FormName . '$' . $wrkname) !== null) {
                return true;
            }
        }
        return Post($wrkname) !== null;
    }

    public function getFormValue(string $name, mixed $default = ""): mixed
    {
        $wrkname = $this->getFormIndexedName($name);
        if ($this->isGridPage() && preg_match(Config("FORM_HIDDEN_INPUT_NAME_PATTERN"), $name)) {
            if (($value = Post($this->FormName . '$' . $wrkname)) !== null) {
                return $value;
            }
        }
        return Post($wrkname, $default);
    }

    public function hasBlankRow(): bool
    {
        return $this->hasFormValue($this->getFormBlankRowName());
    }

    public function hasKeyCount(): bool
    {
        return $this->hasFormValue($this->getFormKeyCountName());
    }

    public function hasRowAction(): bool
    {
        return $this->hasFormValue($this->getFormRowActionName());
    }

    public function getKeyCount(): int
    {
        return intval(Post($this->getFormKeyCountName())); // Name is not indexed
    }

    public function getRowAction(): string
    {
        return $this->getFormValue($this->getFormRowActionName());
    }

    public function getOldKey(): string
    {
        return $this->getFormValue($this->getFormOldKeyName());
    }

    public function getOldRowHash(): string
    {
        return $this->getFormValue($this->getFormRowHashName());
    }

    // Get search value for form element
    public function getSearchValues(string $name): array
    {
        $index = $this->FormIndex;
        $this->FormIndex = -1;
        try {
            return [
                "value" => $this->getFormValue("x_$name"),
                "operator" => $this->getFormValue("z_$name"),
                "condition" => $this->getFormValue("v_$name"),
                "value2" => $this->getFormValue("y_$name"),
                "operator2" => $this->getFormValue("w_$name"),
            ];
        } finally {
            $this->FormIndex = $index;
        }
    }
}
