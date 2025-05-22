<?php

namespace Mateffy\Introspect\DTO;

use Illuminate\Support\Collection;
use Mateffy\Introspect\Facades\Introspect;
use Mateffy\Introspect\Reflection\ModelReflector;

readonly class Model
{
    public function __construct(
        /** @var class-string<\Illuminate\Database\Eloquent\Model> $classpath */
        public string $classpath,

        public ?string $description,

        /** @var Collection<string, ModelProperty> $properties */
        public Collection $properties,

        // TODO: Add relationships, migrations, factories etc.
    ) {}

    public function schema(bool $supportsMultipleTypes = true): array
    {
        $isNormalType = fn (string $type) => match ($type) {
            'string', 'int', 'integer', 'null', 'number', 'float', 'bool', 'boolean', 'array', 'object' => true,
            default => false,
        };

        return array_filter([
            'type' => 'object',
            'title' => basename($this->classpath),
            'description' => $this->description,
            'properties' => $this->properties
                // Filter out properties that have non-normal types
                ->filter(fn (ModelProperty $property) => $property->types
                    ->filter(fn (string $type) => ! $isNormalType($type))
                    ->isEmpty()
                )
                ->map(fn (ModelProperty $property) => array_filter([
                    'type' => $supportsMultipleTypes
                        ? $property->types->all()
                        : $property->types
                            // Sort by not null, so that the first type for sure is not null
                            ->sort(fn (string $type) => $type === 'null' ? 1 : 0)
                            ->first(),
                    'description' => $property->description,
                    'default' => $property->default,
                    'readOnly' => ($property->readable && ! $property->writable) ? true : null,
                    'writeOnly' => ($property->writable && ! $property->readable) ? true : null,
                ]))
                ->toArray(),
            'required' => $this->properties
                ->filter(fn (ModelProperty $property) => $property->types->isEmpty() || ! $property->types->contains('null'))
                ->keys()
                ->toArray(),
        ]);
    }

    public function reflector(): ModelReflector
    {
        return Introspect::model($this->classpath);
    }
}
