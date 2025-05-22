<?php

use Mateffy\Introspect\DTO\ModelProperty;
use Mateffy\Introspect\LaravelIntrospect;
use Mateffy\Introspect\Tests\Fixtures\TestModel;
use Mateffy\Introspect\Tests\Fixtures\TestModelWithDocblock;

it('can see all properties with just eloquent', function () {
    $model = introspect()->model(TestModelWithDocblock::class);

    expect($model->classpath)->toEqual(TestModelWithDocblock::class);
    expect($model->description)->toEqual(<<<'DESCRIPTION'
    This is the short description in the first line of the model.

    This is a second line of the description.
    This is another line.
    DESCRIPTION);
    expect($model->properties)->toHaveCount(7);
});

// if (! function_exists('expectPropertyToBe')) {
//    function expectPropertyToBe(
//        ModelProperty $property,
//        string $name,
//        bool $readable,
//        bool $writable,
//        bool $fillable,
//        bool $hidden,
//        bool $appended,
//        bool $relation,
//        ?string $cast,
//        array $types,
//    ) {
//        expect($property->name)->toBe($name, message: "Property name '{$property->name}' does not match '{$name}'");
//        expect($property->readable)->toBe($readable, message: "Property '{$name}' readable does not match ".json_encode($readable));
//        expect($property->writable)->toBe($writable, message: "Property '{$name}' writable does not match ".json_encode($writable));
//        expect($property->fillable)->toBe($fillable, message: "Property '{$name}' fillable does not match ".json_encode($fillable));
//        expect($property->hidden)->toBe($hidden, message: "Property '{$name}' hidden does not match ".json_encode($hidden));
//        expect($property->appended)->toBe($appended, message: "Property '{$name}' appended does not match ".json_encode($appended));
//        expect($property->relation)->toBe($relation, message: "Property '{$name}' relation does not match ".json_encode($relation));
//        expect($property->cast)->toBe($cast, message: "Property '{$name}' cast does not match ".json_encode($cast));
//
//        // Check that types are EXACTLY the same
//        $propertyTypes = collect($property->types)
//            ->sort()
//            ->values();
//        $expectedTypes = collect($types)
//            ->sort()
//            ->values();
//
//        expect($propertyTypes)->toEqual($expectedTypes, message: "Property '{$name}' types ".json_encode($propertyTypes).' do not match '.json_encode($expectedTypes));
//    }
// }
//
// it('can see all properties with just eloquent', function () {
//    $model = app(LaravelIntrospect::class)->model(TestModel::class);
//    $properties = $model->properties();
//
//    expect($properties)->toHaveCount(7);
//    expect($properties)->toHaveKeys([
//        'id',
//        'name',
//        'email',
//        'password',
//        'remember_token',
//        'created_at',
//        'updated_at',
//    ]);
//
//    expectPropertyToBe(
//        $properties->get('id'),
//        name: 'id',
//        readable: true,
//        writable: true,
//        fillable: false,
//        hidden: false,
//        appended: false,
//        relation: false,
//        cast: 'int',
//        types: ['int'],
//    );
//
//    expectPropertyToBe(
//        $properties->get('name'),
//        name: 'name',
//        readable: true,
//        writable: true,
//        fillable: true,
//        hidden: false,
//        appended: false,
//        relation: false,
//        cast: null,
//        types: [],
//    );
//
//    expectPropertyToBe(
//        $properties->get('email'),
//        name: 'email',
//        readable: true,
//        writable: true,
//        fillable: true,
//        hidden: false,
//        appended: false,
//        relation: false,
//        cast: null,
//        types: [],
//    );
//
//    expectPropertyToBe(
//        $properties->get('password'),
//        name: 'password',
//        readable: true,
//        writable: true,
//        fillable: true,
//        hidden: true,
//        appended: false,
//        relation: false,
//        cast: null,
//        types: [],
//    );
//
//    expectPropertyToBe(
//        $properties->get('remember_token'),
//        name: 'remember_token',
//        readable: true,
//        writable: true,
//        fillable: true,
//        hidden: true,
//        appended: false,
//        relation: false,
//        cast: null,
//        types: [],
//    );
//
//    expectPropertyToBe(
//        $properties->get('created_at'),
//        name: 'created_at',
//        readable: true,
//        writable: true,
//        fillable: false,
//        hidden: false,
//        appended: false,
//        relation: false,
//        cast: DateTimeInterface::class,
//        types: [DateTimeInterface::class],
//    );
//
//    expectPropertyToBe(
//        $properties->get('updated_at'),
//        name: 'updated_at',
//        readable: true,
//        writable: true,
//        fillable: false,
//        hidden: false,
//        appended: false,
//        relation: false,
//        cast: DateTimeInterface::class,
//        types: [DateTimeInterface::class],
//    );
// });
//
// it('can see all properties with eloquent and docblock', function () {
//    $model = app(LaravelIntrospect::class)->model(TestModelWithDocblock::class);
//    $properties = $model->properties();
//
//    expect($properties)->toHaveCount(7);
//    expect($properties)->toHaveKeys([
//        'id',
//        'name',
//        'email',
//        'password',
//        'remember_token',
//        'created_at',
//        'updated_at',
//    ]);
//
//    expectPropertyToBe(
//        $properties->get('id'),
//        name: 'id',
//        readable: true,
//        writable: true,
//        fillable: false,
//        hidden: false,
//        appended: false,
//        relation: false,
//        cast: 'int',
//        types: ['int'],
//    );
//
//    expectPropertyToBe(
//        $properties->get('name'),
//        name: 'name',
//        readable: true,
//        writable: true,
//        fillable: true,
//        hidden: false,
//        appended: false,
//        relation: false,
//        cast: null,
//        types: ['string'],
//    );
//
//    expectPropertyToBe(
//        $properties->get('email'),
//        name: 'email',
//        readable: true,
//        writable: true,
//        fillable: true,
//        hidden: false,
//        appended: false,
//        relation: false,
//        cast: null,
//        types: ['string'],
//    );
//
//    expectPropertyToBe(
//        $properties->get('password'),
//        name: 'password',
//        readable: true,
//        writable: true,
//        fillable: true,
//        hidden: true,
//        appended: false,
//        relation: false,
//        cast: null,
//        types: ['string'],
//    );
//
//    expectPropertyToBe(
//        $properties->get('remember_token'),
//        name: 'remember_token',
//        readable: true,
//        writable: true,
//        fillable: true,
//        hidden: true,
//        appended: false,
//        relation: false,
//        cast: null,
//        types: ['string', 'null'],
//    );
//
//    expectPropertyToBe(
//        $properties->get('created_at'),
//        name: 'created_at',
//        readable: true,
//        writable: false,
//        fillable: false,
//        hidden: false,
//        appended: false,
//        relation: false,
//        cast: DateTimeInterface::class,
//        types: [DateTimeInterface::class],
//    );
//
//    expectPropertyToBe(
//        $properties->get('updated_at'),
//        name: 'updated_at',
//        readable: true,
//        writable: false,
//        fillable: false,
//        hidden: false,
//        appended: false,
//        relation: false,
//        cast: DateTimeInterface::class,
//        types: [DateTimeInterface::class],
//    );
// });
