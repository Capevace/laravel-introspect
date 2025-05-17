<?php

namespace Mateffy\Introspect\Query\Builder;

use Mateffy\Introspect\Query\Where\Models\WhereHasAppendedProperties;
use Mateffy\Introspect\Query\Where\Models\WhereHasFillableProperties;
use Mateffy\Introspect\Query\Where\Models\WhereHasProperties;
use Mateffy\Introspect\Query\Where\Models\WhereHasReadableProperties;
use Mateffy\Introspect\Query\Where\Models\WhereHasRelationProperties;
use Mateffy\Introspect\Query\Where\Models\WhereHasWritableProperties;

trait WhereModels
{
    //
    // General property existance
    //

    public function whereHasProperty(string $properties): static
    {
        return $this->whereHasProperties([$properties]);
    }

    public function whereDoesntHaveProperty(string $properties): static
    {
        return $this->whereDoesntHaveProperties([$properties]);
    }

    public function whereHasProperties(array $properties, bool $all = true): static
    {
        $this->wheres->push(new WhereHasProperties($properties, all: $all));

        return $this;
    }

    public function whereDoesntHaveProperties(array $properties, bool $all = true): static
    {
        $this->wheres->push(new WhereHasProperties($properties, not: true, all: $all));

        return $this;
    }


    //
    // Fillable properties
    //

    public function whereHasFillable(string $properties): static
    {
        return $this->whereHasFillableProperties([$properties]);
    }

    public function whereDoesntHaveFillable(string $properties): static
    {
        return $this->whereDoesntHaveFillableProperties([$properties]);
    }

    public function whereHasFillableProperties(array $properties, bool $all = true): static
    {
        $this->wheres->push(new WhereHasFillableProperties($properties, all: $all));

        return $this;
    }

    public function whereDoesntHaveFillableProperties(array $properties, bool $all = true): static
    {
        $this->wheres->push(new WhereHasFillableProperties($properties, not: true, all: $all));

        return $this;
    }

    //
    // Hidden properties
    //

    public function whereHasHidden(string $properties): static
    {
        return $this->whereHasHiddenProperties([$properties]);
    }

    public function whereDoesntHaveHidden(string $properties): static
    {
        return $this->whereDoesntHaveHiddenProperties([$properties]);
    }

    public function whereHasHiddenProperties(array $properties, bool $all = true): static
    {
        $this->wheres->push(new WhereHasProperties($properties, all: $all));

        return $this;
    }

    public function whereDoesntHaveHiddenProperties(array $properties, bool $all = true): static
    {
        $this->wheres->push(new WhereHasProperties($properties, not: true, all: $all));

        return $this;
    }

    //
    // Appended properties
    //

    public function whereHasAppended(string $properties): static
    {
        return $this->whereHasAppendedProperties([$properties]);
    }

    public function whereDoesntHaveAppended(string $properties): static
    {
        return $this->whereDoesntHaveAppendedProperties([$properties]);
    }

    public function whereHasAppendedProperties(array $properties, bool $all = true): static
    {
        $this->wheres->push(new WhereHasAppendedProperties($properties, all: $all));

        return $this;
    }

    public function whereDoesntHaveAppendedProperties(array $properties, bool $all = true): static
    {
        $this->wheres->push(new WhereHasAppendedProperties($properties, not: true, all: $all));

        return $this;
    }

    //
    // Readable properties
    //

    public function whereHasReadable(string $properties): static
    {
        return $this->whereHasReadableProperties([$properties]);
    }

    public function whereDoesntHaveReadable(string $properties): static
    {
        return $this->whereDoesntHaveReadableProperties([$properties]);
    }

    public function whereHasReadableProperties(array $properties, bool $all = true): static
    {
        $this->wheres->push(new WhereHasReadableProperties($properties, all: $all));

        return $this;
    }

    public function whereDoesntHaveReadableProperties(array $properties, bool $all = true): static
    {
        $this->wheres->push(new WhereHasReadableProperties($properties, not: true, all: $all));

        return $this;
    }

    //
    // Writable properties
    //

    public function whereHasWritable(string $properties): static
    {
        return $this->whereHasWritableProperties([$properties]);
    }

    public function whereDoesntHaveWritable(string $properties): static
    {
        return $this->whereDoesntHaveWritableProperties([$properties]);
    }

    public function whereHasWritableProperties(array $properties, bool $all = true): static
    {
        $this->wheres->push(new WhereHasWritableProperties($properties, all: $all));

        return $this;
    }

    public function whereDoesntHaveWritableProperties(array $properties, bool $all = true): static
    {
        $this->wheres->push(new WhereHasWritableProperties($properties, not: true, all: $all));

        return $this;
    }

    //
    // Relationships
    //

    public function whereHasRelationship(string $relationship): static
    {
        $this->wheres->push(new WhereHasRelationProperties([$relationship]));

        return $this;
    }

    public function whereDoesntHaveRelationship(string $relationship): static
    {
        $this->wheres->push(new WhereHasRelationProperties([$relationship], not: true));

        return $this;
    }

    // We skip the plural definitions for now, while we work on better relationship support.
    // I want to avoid breaking change and the whereHasRelationship(s) methods should accept more paramters,
    // like relationship type and related model.
    // For now, only relationship presence is checked.

//    public function whereHasRelationships(array $relationships, bool $all = true): static
//    {
//        $this->wheres->push(new WhereHasProperties($relationships, all: $all));
//
//        return $this;
//    }
//
//    public function whereDoesntHaveRelationships(array $relationships, bool $all = true): static
//    {
//        $this->wheres->push(new WhereHasProperties($relationships, not: true, all: $all));
//
//        return $this;
//    }

    // TODO: add things like
}
