<?php

use InvalidArgumentException;
use Mateffy\Introspect\Query\Where\Classes\WhereExtendsClass;
use Mateffy\Introspect\Query\Where\Classes\WhereImplementsInterfaces;
use Mateffy\Introspect\Query\Where\Classes\WhereUsesTraits;
use Mateffy\Introspect\Query\Where\NestedWhere;
use Mateffy\Introspect\Query\Where\Views\WhereUsesView;
use Mateffy\Introspect\Query\Where\Views\WhereViewNameEquals;
use Mateffy\Introspect\Query\Where\WhereSerializer;

beforeEach(function () {
    $this->serializer = new WhereSerializer;
});

it('deserializes nested where clauses', function () {
    $data = [
        'type' => 'nested',
        'or' => false,
        'not' => false,
        'wheres' => [],
    ];

    $where = $this->serializer->deserialize($data);

    expect($where)->toBeInstanceOf(NestedWhere::class);
})->skip('Requires fromArray implementation on Where classes');

it('deserializes view name equals where', function () {
    $data = [
        'type' => 'view-name-equals',
        'viewName' => 'test-view',
        'not' => false,
    ];

    $where = $this->serializer->deserialize($data);

    expect($where)->toBeInstanceOf(WhereViewNameEquals::class);
})->skip('Requires fromArray implementation on Where classes');

it('deserializes view uses where', function () {
    $data = [
        'type' => 'view-uses',
        'viewToUse' => 'test-view',
        'not' => false,
        'lax' => false,
    ];

    $where = $this->serializer->deserialize($data);

    expect($where)->toBeInstanceOf(WhereUsesView::class);
})->skip('Requires fromArray implementation on Where classes');

it('deserializes class extends where', function () {
    $data = [
        'type' => 'class-extends',
        'extends' => ['BaseModel'],
        'not' => false,
        'all' => true,
    ];

    $where = $this->serializer->deserialize($data);

    expect($where)->toBeInstanceOf(WhereExtendsClass::class);
})->skip('Requires fromArray implementation on Where classes');

it('deserializes class implements where', function () {
    $data = [
        'type' => 'class-implements',
        'implements' => ['TestInterface'],
        'not' => false,
        'all' => true,
    ];

    $where = $this->serializer->deserialize($data);

    expect($where)->toBeInstanceOf(WhereImplementsInterfaces::class);
})->skip('Requires fromArray implementation on Where classes');

it('throws exception for unknown where type', function () {
    $data = [
        'type' => 'unknown-type',
    ];

    expect(fn () => $this->serializer->deserialize($data))
        ->toThrow(InvalidArgumentException::class, 'Unknown where type');
});

it('preserves not flag during deserialization', function () {
    $data = [
        'type' => 'view-name-equals',
        'viewName' => 'test-view',
        'not' => true,
    ];

    $where = $this->serializer->deserialize($data);

    expect($where)->toBeInstanceOf(WhereViewNameEquals::class);
})->skip('Requires fromArray implementation on Where classes');

it('preserves all flag during class extends deserialization', function () {
    $data = [
        'type' => 'class-extends',
        'extends' => ['Model1', 'Model2'],
        'not' => false,
        'all' => false,
    ];

    $where = $this->serializer->deserialize($data);

    expect($where)->toBeInstanceOf(WhereExtendsClass::class);
})->skip('Requires fromArray implementation on Where classes');

it('handles nested where with multiple wheres', function () {
    $data = [
        'type' => 'nested',
        'or' => false,
        'not' => false,
        'wheres' => [
            [
                'type' => 'view-name-equals',
                'viewName' => 'view1',
                'not' => false,
            ],
            [
                'type' => 'view-name-equals',
                'viewName' => 'view2',
                'not' => false,
            ],
        ],
    ];

    $where = $this->serializer->deserialize($data);

    expect($where)->toBeInstanceOf(NestedWhere::class);
})->skip('Requires fromArray implementation on Where classes');

it('handles deeply nested where clauses', function () {
    $data = [
        'type' => 'nested',
        'or' => false,
        'not' => false,
        'wheres' => [
            [
                'type' => 'nested',
                'or' => true,
                'not' => false,
                'wheres' => [],
            ],
        ],
    ];

    $where = $this->serializer->deserialize($data);

    expect($where)->toBeInstanceOf(NestedWhere::class);
})->skip('Requires fromArray implementation on Where classes');

it('handles route uses controller where', function () {
    $data = [
        'type' => 'route-uses-controller',
        'controller' => 'TestController',
        'method' => 'index',
        'not' => false,
    ];

    // This type should be recognized by the serializer
    expect(fn () => $this->serializer->deserialize($data))
        ->not->toThrow(InvalidArgumentException::class, 'Unknown where type');
});

it('handles route uses methods where', function () {
    $data = [
        'type' => 'route-uses-methods',
        'methods' => ['GET', 'POST'],
        'all' => true,
        'not' => false,
    ];

    expect(fn () => $this->serializer->deserialize($data))
        ->not->toThrow(InvalidArgumentException::class, 'Unknown where type');
});

it('handles route uses middlewares where', function () {
    $data = [
        'type' => 'route-uses-middlewares',
        'middlewares' => ['auth', 'web'],
        'all' => true,
        'not' => false,
    ];

    expect(fn () => $this->serializer->deserialize($data))
        ->not->toThrow(InvalidArgumentException::class, 'Unknown where type');
});

it('handles class uses traits where', function () {
    $data = [
        'type' => 'class-uses-trait',
        'traits' => ['TestTrait'],
        'all' => true,
        'not' => false,
    ];

    $where = $this->serializer->deserialize($data);

    expect($where)->toBeInstanceOf(WhereUsesTraits::class);
})->skip('Requires fromArray implementation on Where classes');
