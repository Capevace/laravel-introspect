<?php

namespace Mateffy\Introspect\Query\Where;

use Illuminate\Support\Collection;
use Mateffy\Introspect\Query\Builder\WhereBuilder;
use Mateffy\Introspect\Query\Query;
use Mateffy\Introspect\Query\Where;
use Mateffy\Introspect\Query\Where\Concerns\NotInverter;
use Roave\BetterReflection\Reflection\ReflectionClass;

class NestedWhere implements Where, Query
{
    use NotInverter;
	use WhereBuilder;

	public function __construct(array|Collection $wheres, bool $or = false, public bool $not = false)
	{
		$this->or = $or;
		$this->wheres = $wheres;
	}

    public function createSubquery(): Query
    {
        return new NestedWhere(
            wheres: collect(),
            or: $this->or,
            not: $this->not
        );
    }

    public function filter($value): bool
	{
        $results = $this->wheres
            ->map(fn (Where $where) => $where->filter($value));

        return $this->invert(
            condition: $this->or
                ? $results->contains(true)
                : $results->every(fn (bool $result) => $result),
            not: $this->not
        );
	}

    public function toArray(): array
    {
        return [
            'type' => 'nested',
            'or' => $this->or,
            'not' => $this->not,
            'wheres' => $this->wheres
                ->map(fn (Where $where) => $where->toArray())
                ->all(),
        ];
    }

    public static function fromArray(array $data): static
    {
        $instance = new static(or: $data['or'] ?? false, not: $data['not'] ?? false);

        foreach ($data['wheres'] ?? [] as $where) {
            $instance->addWhere(
                Where::fromArray($where)
            );
        }

        return $instance;
    }
}
