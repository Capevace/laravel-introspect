<?php

namespace Mateffy\Introspect\Query\Contracts;

interface ModelQueryInterface extends ClassQueryInterface
{
    public function whereHasProperty(string $properties): static;

    public function whereDoesntHaveProperty(string $properties): static;

    public function whereHasProperties(array $properties, bool $all = true): static;

    public function whereDoesntHaveProperties(array $properties, bool $all = true): static;

    public function whereHasFillable(string $properties): static;

    public function whereDoesntHaveFillable(string $properties): static;

    public function whereHasFillableProperties(array $properties, bool $all = true): static;

    public function whereDoesntHaveFillableProperties(array $properties, bool $all = true): static;

    public function whereHasHidden(string $properties): static;

    public function whereDoesntHaveHidden(string $properties): static;

    public function whereHasHiddenProperties(array $properties, bool $all = true): static;

    public function whereDoesntHaveHiddenProperties(array $properties, bool $all = true): static;

    public function whereHasAppended(string $properties): static;

    public function whereDoesntHaveAppended(string $properties): static;

    public function whereHasAppendedProperties(array $properties, bool $all = true): static;

    public function whereDoesntHaveAppendedProperties(array $properties, bool $all = true): static;

    public function whereHasReadable(string $properties): static;

    public function whereDoesntHaveReadable(string $properties): static;

    public function whereHasReadableProperties(array $properties, bool $all = true): static;

    public function whereDoesntHaveReadableProperties(array $properties, bool $all = true): static;

    public function whereHasWritable(string $properties): static;

    public function whereDoesntHaveWritable(string $properties): static;

    public function whereHasWritableProperties(array $properties, bool $all = true): static;

    public function whereDoesntHaveWritableProperties(array $properties, bool $all = true): static;

    public function whereHasRelationship(string $relationship): static;

    public function whereDoesntHaveRelationship(string $relationship): static;
}
