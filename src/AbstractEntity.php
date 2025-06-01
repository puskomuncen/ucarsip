<?php

namespace PHPMaker2025\ucarsip;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManager;
use ReflectionClass;
use ReflectionProperty;
use ReflectionAttribute;
use ArrayAccess;

/**
 * Abstract entity class
 */
abstract class AbstractEntity implements ArrayAccess
{
    /**
     * Get reflection class
     *
     * @return ReflectionClass
     */
    public function reflectionClass(): ReflectionClass
    {
        return new ReflectionClass($this);
    }

    /**
     * Get database ID
     *
     * @return string
     */
    public function databaseId(): string
    {
        $attributes = $this->reflectionClass()->getAttributes(Table::class, ReflectionAttribute::IS_INSTANCEOF);
        foreach ($attributes as $attribute) {
            return $attribute->newInstance()->options["dbId"] ?? "DB";
        }
        return "DB";
    }

    /**
     * Get entity manager
     *
     * @return EntityManager
     */
    public function entityManager(): EntityManager
    {
        return EntityManager($this->databaseId());
    }

    /**
     * Get meta data
     */
    public function metaData(): ClassMetadata
    {
        return $this->entityManager()->getMetadataFactory()->getMetadataFor(get_class($this));
    }

    /**
     * Check if column is initialized
     *
     * @param string $name Column name
     * @return bool
     */
    public function isInitialized(string $name): bool
    {
        $fieldName = $this->metaData()->getFieldName($name);
        $reflField = $this->metaData()->getReflectionProperty($fieldName);
        return $reflField?->isInitialized($this) ?? false;
    }

    /**
     * Get primary key value
     * Note: Return the first primary key only, does not support composite key.
     *
     * @return mixed
     */
    public function id(): mixed
    {
        return array_values($this->metaData()->getIdentifierValues($this))[0] ?? null;
    }

    /**
     * Get primary key value(s)
     * Note: Return the primary key as array (support composite key)
     *
     * @return array
     */
    public function identifierValues(): array
    {
        return $this->metaData()->getIdentifierValues($this);
    }

    /**
     * Get the field name for a column name
     * Note: If no field name can be found the column name is returned.
     *
     * @return string
     */
    public function fieldName(string $columnName): string
    {
        return $this->metaData()->fieldNames[$columnName] ?? $columnName;
    }

    /**
     * Get value by column name
     *
     * @param string $name Column name
     * @return mixed
     */
    public function get(string $name): mixed
    {
        $method = "get" . $this->fieldName($name); // Method name is case-insensitive
        if (method_exists($this, $method)) {
            return $this->$method();
        }
        return null;
    }

    /**
     * Set value by column name
     *
     * @param string $name Column name
     * @param mixed $value Value
     * @return static
     */
    public function set(string $name, mixed $value): static
    {
        $method = "set" . $this->fieldName($name); // Method name is case-insensitive
        if (method_exists($this, $method)) {
            $this->$method($value);
        }
        return $this;
    }

    /**
     * Convert to array with column name as keys
     *
     * @return array
     */
    public function toArray(): array
    {
        $fieldNames = array_keys($this->metaData()->fieldNames);
        return array_combine($fieldNames, array_map(fn ($name) => $this->isInitialized($name) ? $this->get($name) : null, $fieldNames));
    }

    /**
     * Offset exists
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->metaData()->fieldNames);
    }

    /**
     * Offset get
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    /**
     * Offset set
     *
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set($offset, $value);
    }

    /**
     * Offset unset
     *
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        $fieldName = $this->fieldName($offset);
        unset($this->$fieldName);
    }
}
