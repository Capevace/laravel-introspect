<?php

use Mateffy\Introspect\DTO\ModelProperty;

it('can create a model property DTO', function () {
    $property = new ModelProperty(
        name: 'title',
        description: 'The title field',
        default: 'Untitled',
        readable: true,
        writable: true,
        fillable: true,
        hidden: false,
        appended: false,
        relation: false,
        cast: 'string',
        types: collect(['string'])
    );

    expect($property->name)->toBe('title')
        ->and($property->description)->toBe('The title field')
        ->and($property->default)->toBe('Untitled')
        ->and($property->readable)->toBeTrue()
        ->and($property->writable)->toBeTrue()
        ->and($property->fillable)->toBeTrue()
        ->and($property->hidden)->toBeFalse()
        ->and($property->appended)->toBeFalse()
        ->and($property->relation)->toBeFalse()
        ->and($property->cast)->toBe('string')
        ->and($property->types)->toEqual(collect(['string']));
});

it('can create a hidden property', function () {
    $property = new ModelProperty(
        name: 'password',
        description: null,
        default: null,
        readable: true,
        writable: true,
        fillable: true,
        hidden: true,
        appended: false,
        relation: false,
        cast: 'hashed',
        types: collect(['string'])
    );

    expect($property->hidden)->toBeTrue()
        ->and($property->name)->toBe('password');
});

it('can create a read-only property', function () {
    $property = new ModelProperty(
        name: 'created_at',
        description: null,
        default: null,
        readable: true,
        writable: false,
        fillable: false,
        hidden: false,
        appended: false,
        relation: false,
        cast: 'datetime',
        types: collect(['string'])
    );

    expect($property->readable)->toBeTrue()
        ->and($property->writable)->toBeFalse();
});

it('can create a write-only property', function () {
    $property = new ModelProperty(
        name: 'verification_code',
        description: null,
        default: null,
        readable: false,
        writable: true,
        fillable: true,
        hidden: true,
        appended: false,
        relation: false,
        cast: null,
        types: collect(['string'])
    );

    expect($property->readable)->toBeFalse()
        ->and($property->writable)->toBeTrue();
});

it('can create a relation property', function () {
    $property = new ModelProperty(
        name: 'user',
        description: 'The related user',
        default: null,
        readable: true,
        writable: false,
        fillable: false,
        hidden: false,
        appended: true,
        relation: true,
        cast: null,
        types: collect(['User'])
    );

    expect($property->relation)->toBeTrue()
        ->and($property->appended)->toBeTrue()
        ->and($property->types->first())->toBe('User');
});

it('can handle null values for optional fields', function () {
    $property = new ModelProperty(
        name: 'simple_field',
        description: null,
        default: null,
        readable: true,
        writable: true,
        fillable: false,
        hidden: false,
        appended: false,
        relation: false,
        cast: null,
        types: collect([])
    );

    expect($property->description)->toBeNull()
        ->and($property->default)->toBeNull()
        ->and($property->cast)->toBeNull()
        ->and($property->types)->toBeEmpty();
});

it('can handle multiple types', function () {
    $property = new ModelProperty(
        name: 'optional_string',
        description: null,
        default: null,
        readable: true,
        writable: true,
        fillable: true,
        hidden: false,
        appended: false,
        relation: false,
        cast: null,
        types: collect(['string', 'null'])
    );

    expect($property->types)->toHaveCount(2)
        ->toContain('string')
        ->toContain('null');
});

it('can create a fillable property', function () {
    $property = new ModelProperty(
        name: 'email',
        description: 'Email address',
        default: null,
        readable: true,
        writable: true,
        fillable: true,
        hidden: false,
        appended: false,
        relation: false,
        cast: null,
        types: collect(['string'])
    );

    expect($property->fillable)->toBeTrue();
});

it('can create a non-fillable property', function () {
    $property = new ModelProperty(
        name: 'id',
        description: 'Primary key',
        default: null,
        readable: true,
        writable: false,
        fillable: false,
        hidden: false,
        appended: false,
        relation: false,
        cast: null,
        types: collect(['int'])
    );

    expect($property->fillable)->toBeFalse();
});

it('can create a non-appended property', function () {
    $property = new ModelProperty(
        name: 'name',
        description: 'The name',
        default: null,
        readable: true,
        writable: true,
        fillable: true,
        hidden: false,
        appended: false,
        relation: false,
        cast: null,
        types: collect(['string'])
    );

    expect($property->appended)->toBeFalse();
});

it('handles complex types like arrays', function () {
    $property = new ModelProperty(
        name: 'metadata',
        description: 'JSON metadata',
        default: null,
        readable: true,
        writable: true,
        fillable: true,
        hidden: false,
        appended: false,
        relation: false,
        cast: 'array',
        types: collect(['array'])
    );

    expect($property->cast)->toBe('array')
        ->and($property->types->first())->toBe('array');
});

it('handles boolean types', function () {
    $property = new ModelProperty(
        name: 'is_active',
        description: 'Active status',
        default: true,
        readable: true,
        writable: true,
        fillable: true,
        hidden: false,
        appended: false,
        relation: false,
        cast: 'boolean',
        types: collect(['bool'])
    );

    expect($property->cast)->toBe('boolean')
        ->and($property->types->first())->toBe('bool');
    // Default can be true or '1' depending on PHP version/casting
    expect((bool) $property->default)->toBeTrue();
});
