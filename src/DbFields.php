<?php

namespace PHPMaker2025\ucarsip;

use ArrayObject;

/**
 * DbField collection
 */
class DbFields extends ArrayObject
{
    // Get property values
    public function getPropertyValues(string $propertyName): array
    {
        $values = [];
        foreach ($this as $fldname => $fld) {
            $values[$fldname] = $fld->$propertyName;
        }
        return $values;
    }

    // Get current values
    public function getCurrentValues(): array
    {
        return $this->getPropertyValues("CurrentValue");
    }

    // Set current values (for number/date/time fields only)
    public function setCurrentValues(array $row): void
    {
        foreach ($row as $fldname => $value) {
            if (isset($this[$fldname]) && in_array($this[$fldname]->DataType, [DataType::NUMBER, DataType::DATE, DataType::TIME])) {
                $this[$fldname]->CurrentValue = $value;
            }
        }
    }

    // Get form values
    public function getFormValues(): array
    {
        return $this->getPropertyValues("FormValue");
    }

    // Get database values
    public function getDbValues(): array
    {
        return $this->getPropertyValues("DbValue");
    }

    // Get view values
    public function getViewValues(): array
    {
        return $this->getPropertyValues("ViewValue");
    }

    // Set a property of all fields
    public function setPropertyValues(string $propertyName, mixed $value): void
    {
        foreach ($this as $fld) {
            if (property_exists($fld, $propertyName)) {
                $fld->$propertyName = $value;
            } elseif (method_exists($fld, $propertyName)) {
                $fld->$propertyName($value);
            }
        }
    }
}
