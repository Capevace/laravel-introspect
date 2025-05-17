<?php

namespace Mateffy\Introspect\Reflection\ModelReflector;

use DateTimeInterface;
use Illuminate\Support\Collection;
use Mateffy\Introspect\DTO\ModelProperty;
use phpDocumentor\Reflection\DocBlock\Tags\Property;
use phpDocumentor\Reflection\DocBlock\Tags\PropertyRead;
use phpDocumentor\Reflection\DocBlock\Tags\PropertyWrite;
use phpDocumentor\Reflection\DocBlock\Tags\TagWithType;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\AggregatedType;
use phpDocumentor\Reflection\Types\Nullable;
use ReflectionClass;
use ReflectionException;

use function Livewire\invade;

trait HasProperties
{
    public const DEFAULT_TO_STRING = false;

    /**
     * @throws ReflectionException
     */
    public function properties(): Collection
    {
        $propertiesFromDocblock = $this->parsePropertiesFromDocblock()
            ->keyBy('name');

        $propertiesFromEloquent = $this->parsePropertiesFromEloquent()
            ->keyBy('name');

        $all = $propertiesFromDocblock
            ->pluck('name')
            ->merge($propertiesFromEloquent->pluck('name'))
            ->unique()
            ->values();

        $properties = collect();

        foreach ($all as $property) {
            $docblockProperty = $propertiesFromDocblock->get($property);
            $eloquentProperty = $propertiesFromEloquent->get($property);

            if ($docblockProperty && $eloquentProperty) {
                $properties[$property] = new ModelProperty(
                    name: $property,
                    description: $docblockProperty->description,
                    default: $eloquentProperty->default,
                    readable: $docblockProperty->readable,
                    writable: $docblockProperty->writable,
                    fillable: $eloquentProperty->fillable,
                    hidden: $eloquentProperty->hidden,
                    appended: $eloquentProperty->appended,
                    relation: $eloquentProperty->relation,
                    cast: $eloquentProperty->cast,
                    types: $docblockProperty->types
                        ->merge($eloquentProperty->types)
                        ->unique()
                        ->values()
                );
            } elseif ($docblockProperty) {
                $properties[$property] = $docblockProperty;
            } elseif ($eloquentProperty) {
                $properties[$property] = $eloquentProperty;
            }
        }

        return $properties;
    }

    protected function parsePropertiesFromEloquent(): Collection
    {
        $fillable = $this->instance->getFillable();
        $hidden = $this->instance->getHidden();
        $guarded = $this->instance->getGuarded();
        $casts = $this->instance->getCasts();
        $dates = $this->instance->getDates();
        $appends = $this->instance->getAppends();
        $visible = $this->instance->getVisible();
        $relations = $this->instance->getRelations();
        $usesTimestamps = $this->instance->usesTimestamps();
        $defaultAttributes = invade($this->instance)->attributes;

        return collect([
            ...$fillable,
            ...$hidden,
            ...$guarded,
            ...array_keys($casts),
            ...$dates,
            ...$appends,
            ...$visible,
            ...$relations,
            ...($usesTimestamps
                ? [$this->instance->getCreatedAtColumn(), $this->instance->getUpdatedAtColumn()]
                : []
            ),
            ...array_keys($defaultAttributes),
        ])
            ->unique()
            ->filter(fn (string $property) => $property !== '*')
            ->values()
            ->map(function (string $property) use ($fillable, $hidden, $guarded, $casts, $dates, $appends, $visible, $relations, $defaultAttributes) {
                $isFillable = in_array($property, $fillable);
                $isHidden = in_array($property, $hidden);
                $isGuarded = in_array($property, $guarded);
                $isDate = in_array($property, $dates);
                $isAppended = in_array($property, $appends);
                $isVisible = in_array($property, $visible);
                $isRelation = in_array($property, $relations);

                $isActuallyFillable = $isFillable && ! $isGuarded;
                $isActuallyHidden = $isHidden && ! $isVisible;
                $cast = $casts[$property] ?? null;

                $types = collect();

                if ($isDate) {
                    $types->push(DateTimeInterface::class);
                }

                if ($cast) {
                    $types->push($cast);
                }

                if ($types->isEmpty() && self::DEFAULT_TO_STRING) {
                    $types->push('string');
                }

                return new ModelProperty(
                    name: $property,
                    description: null,
                    default: $defaultAttributes[$property] ?? null,
                    readable: true,
                    writable: true,
                    fillable: $isActuallyFillable,
                    hidden: $isActuallyHidden,
                    appended: $isAppended,
                    relation: $isRelation,
                    cast: $cast ?? ($isDate ? DateTimeInterface::class : null),
                    types: $types
                        ->unique()
                        ->values()
                );
            });
    }

    /**
     * @throws ReflectionException
     */
    protected function parsePropertiesFromDocblock(): Collection
    {
        $factory = DocBlockFactory::createInstance();
        $reflection = new ReflectionClass($this->model);
        $comment = $reflection->getDocComment();

        if ($comment === false) {
            return collect();
        }

        $docblock = $factory->create($comment);

        $properties = collect($docblock->getTagsWithTypeByName('property'))
            ->filter(fn (TagWithType $tag) => $tag instanceof Property)
            ->values();

        $readOnlyProperties = collect($docblock->getTagsWithTypeByName('property-read'))
            ->filter(fn (TagWithType $tag) => $tag instanceof PropertyRead)
            ->values();

        $writeOnlyProperties = collect($docblock->getTagsWithTypeByName('property-write'))
            ->filter(fn (TagWithType $tag) => $tag instanceof PropertyWrite)
            ->values();

        $all = collect();

        $all->push(...$this->mapProperties($properties));
        $all->push(...$this->mapProperties($readOnlyProperties));
        $all->push(...$this->mapProperties($writeOnlyProperties));

        return $all->unique('name');
    }

    /**
     * @param  Collection<Property|PropertyRead|PropertyWrite>  $properties
     * @return Collection<ModelProperty>
     */
    protected function mapProperties(Collection $properties): Collection
    {
        $all = collect();

        foreach ($properties as $property) {
            $name = $property->getVariableName();
            $type = $property->getType();

            if ($property instanceof PropertyRead) {
                $readable = true;
                $writable = false;
            } elseif ($property instanceof PropertyWrite) {
                $readable = false;
                $writable = true;
            } else {
                $readable = true;
                $writable = true;
            }

            $normalizeType = function (Type $type): Collection {
                if ($type instanceof AggregatedType) {
                    return collect($type->getIterator())
                        ->map(fn (Type $subtype) => $subtype->__toString());
                } else {
                    return collect([$type->__toString()]);
                }
            };

            if ($type instanceof Nullable) {
                $type = $type->getActualType();
                $types = $normalizeType($type);
                $types->push('null');
            } else {
                $types = $normalizeType($type);
            }

            $all->push(new ModelProperty(
                name: $name,
                description: $property->getDescription(),
                default: null,
                readable: $readable,
                writable: $writable,
                fillable: false,
                hidden: false,
                appended: false,
                relation: false,
                cast: null,
                types: $types
                    // Types from the docblock are prefixed with a backslash, whereas ::class does not.
                    // We normalize to the native PHP class name.
                    ->map(fn (string $type) => ltrim($type, '\\'))
            ));
        }

        return $all;
    }
}
