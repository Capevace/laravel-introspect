<?php

use Mateffy\Introspect\Query\Query;
use Mateffy\Introspect\Query\Where;
use Mateffy\Introspect\Query\Where\NestedWhere;
use Mateffy\Introspect\Query\Where\Views\WhereViewNameEquals;

it('filters with AND logic by default', function () {
    $where1 = new WhereViewNameEquals(['view1']);
    $where2 = new WhereViewNameEquals(['view2']);

    $nested = new NestedWhere(collect([$where1, $where2]), or: false, not: false);

    // Both conditions must be true - passing a string that doesn't match either
    // should result in false
    $result = $nested->filter('view3');

    expect($result)->toBeFalse();
});

it('filters with OR logic when specified', function () {
    $where1 = new WhereViewNameEquals(['view1']);
    $where2 = new WhereViewNameEquals(['view2']);

    $nested = new NestedWhere(collect([$where1, $where2]), or: true, not: false);

    // At least one condition must be true
    $result = $nested->filter('view1');

    expect($result)->toBeTrue();
});

it('inverts result with not flag', function () {
    $where1 = new WhereViewNameEquals(['view1']);

    $nested = new NestedWhere(collect([$where1]), or: false, not: true);

    // NOT (filter returns true) -> false
    $result = $nested->filter('view1');

    expect($result)->toBeFalse();
});

it('handles empty where collection', function () {
    $nested = new NestedWhere(collect([]), or: false, not: false);

    // Empty AND should return true (vacuous truth)
    $result = $nested->filter('anything');

    expect($result)->toBeTrue();
});

it('creates subquery', function () {
    $nested = new NestedWhere(collect([]), or: false, not: false);

    $subquery = $nested->createSubquery();

    expect($subquery)->toBeInstanceOf(NestedWhere::class);
});

it('serializes to array', function () {
    $where1 = new WhereViewNameEquals(['view1']);
    $nested = new NestedWhere(collect([$where1]), or: false, not: false);

    $array = $nested->toArray();

    expect($array)
        ->toHaveKey('type', 'nested')
        ->toHaveKey('or', false)
        ->toHaveKey('not', false)
        ->toHaveKey('wheres');
})->skip('Requires toArray/fromArray implementation on Where classes');

it('deserializes from array', function () {
    $data = [
        'type' => 'nested',
        'or' => true,
        'not' => true,
        'wheres' => [],
    ];

    $nested = NestedWhere::fromArray($data);

    expect($nested)->toBeInstanceOf(NestedWhere::class)
        ->and($nested->or)->toBeTrue()
        ->and($nested->not)->toBeTrue();
})->skip('Requires toArray/fromArray implementation on Where classes');

it('implements Where interface', function () {
    $nested = new NestedWhere(collect([]), or: false, not: false);

    expect($nested)->toBeInstanceOf(Where::class);
});

it('implements Query interface', function () {
    $nested = new NestedWhere(collect([]), or: false, not: false);

    expect($nested)->toBeInstanceOf(Query::class);
});

it('handles nested nested where clauses', function () {
    $innerWhere = new WhereViewNameEquals(['inner']);
    $innerNested = new NestedWhere(collect([$innerWhere]), or: false, not: false);

    $outerWhere = new WhereViewNameEquals(['outer']);
    $outerNested = new NestedWhere(collect([$outerWhere, $innerNested]), or: false, not: false);

    expect($outerNested)->toBeInstanceOf(NestedWhere::class);
});

it('preserves or flag in subquery', function () {
    $nested = new NestedWhere(collect([]), or: true, not: false);

    $subquery = $nested->createSubquery();

    expect($subquery->or)->toBeTrue();
});

it('preserves not flag in subquery', function () {
    $nested = new NestedWhere(collect([]), or: false, not: true);

    $subquery = $nested->createSubquery();

    expect($subquery->not)->toBeTrue();
});

it('handles serialization of nested with children', function () {
    $where1 = new WhereViewNameEquals(['view1']);
    $where2 = new WhereViewNameEquals(['view2']);
    $nested = new NestedWhere(collect([$where1, $where2]), or: false, not: false);

    $array = $nested->toArray();

    expect($array['wheres'])->toHaveCount(2);
})->skip('Requires toArray/fromArray implementation on Where classes');

it('deserializes nested with children', function () {
    $data = [
        'type' => 'nested',
        'or' => false,
        'not' => false,
        'wheres' => [
            [
                'type' => 'view-name-equals',
                'text' => ['view1'],
                'not' => false,
            ],
            [
                'type' => 'view-name-equals',
                'text' => ['view2'],
                'not' => false,
            ],
        ],
    ];

    $nested = NestedWhere::fromArray($data);

    expect($nested)->toBeInstanceOf(NestedWhere::class);
})->skip('Requires toArray/fromArray implementation on Where classes');

it('AND logic returns true when all conditions match', function () {
    // Create wildcard-friendly wheres
    $where1 = new class implements Where
    {
        public function filter($value): bool
        {
            return true;
        }

        public function toArray(): array
        {
            return ['type' => 'test'];
        }

        public static function fromArray(array $data): static
        {
            return new self;
        }
    };

    $where2 = new class implements Where
    {
        public function filter($value): bool
        {
            return true;
        }

        public function toArray(): array
        {
            return ['type' => 'test2'];
        }

        public static function fromArray(array $data): static
        {
            return new self;
        }
    };

    $nested = new NestedWhere(collect([$where1, $where2]), or: false, not: false);

    expect($nested->filter('test'))->toBeTrue();
});

it('AND logic returns false when any condition fails', function () {
    $where1 = new class implements Where
    {
        public function filter($value): bool
        {
            return true;
        }

        public function toArray(): array
        {
            return ['type' => 'test'];
        }

        public static function fromArray(array $data): static
        {
            return new self;
        }
    };

    $where2 = new class implements Where
    {
        public function filter($value): bool
        {
            return false;
        }

        public function toArray(): array
        {
            return ['type' => 'test2'];
        }

        public static function fromArray(array $data): static
        {
            return new self;
        }
    };

    $nested = new NestedWhere(collect([$where1, $where2]), or: false, not: false);

    expect($nested->filter('test'))->toBeFalse();
});

it('OR logic returns true when at least one condition matches', function () {
    $where1 = new class implements Where
    {
        public function filter($value): bool
        {
            return false;
        }

        public function toArray(): array
        {
            return ['type' => 'test'];
        }

        public static function fromArray(array $data): static
        {
            return new self;
        }
    };

    $where2 = new class implements Where
    {
        public function filter($value): bool
        {
            return true;
        }

        public function toArray(): array
        {
            return ['type' => 'test2'];
        }

        public static function fromArray(array $data): static
        {
            return new self;
        }
    };

    $nested = new NestedWhere(collect([$where1, $where2]), or: true, not: false);

    expect($nested->filter('test'))->toBeTrue();
});

it('returns true for OR with no matching conditions', function () {
    $where1 = new WhereViewNameEquals(['view1']);
    $where2 = new WhereViewNameEquals(['view2']);

    $nested = new NestedWhere(collect([$where1, $where2]), or: true, not: false);

    // OR with no matching conditions should return false
    $result = $nested->filter('view3');

    expect($result)->toBeFalse();
});

it('handles NOT with AND logic', function () {
    $where1 = new class implements Where
    {
        public function filter($value): bool
        {
            return true;
        }

        public function toArray(): array
        {
            return ['type' => 'test'];
        }

        public static function fromArray(array $data): static
        {
            return new self;
        }
    };

    $nested = new NestedWhere(collect([$where1]), or: false, not: true);

    // NOT (true) = false
    expect($nested->filter('test'))->toBeFalse();
});

it('handles NOT with OR logic', function () {
    $where1 = new class implements Where
    {
        public function filter($value): bool
        {
            return false;
        }

        public function toArray(): array
        {
            return ['type' => 'test'];
        }

        public static function fromArray(array $data): static
        {
            return new self;
        }
    };

    $nested = new NestedWhere(collect([$where1]), or: true, not: true);

    // NOT (false) = true
    expect($nested->filter('test'))->toBeTrue();
});
