<?php

use Mateffy\Introspect\DTO\Test;
use Mateffy\Introspect\DTO\TestLocation;
use Mateffy\Introspect\DTO\TestMetadata;
use Mateffy\Introspect\DTO\TestReference;

// =============================================================================
// TestLocation DTO Tests
// =============================================================================

it('creates TestLocation with minimal fields', function () {
    $location = new TestLocation(file: 'tests/ExampleTest.php');
    
    expect($location->file)->toBe('tests/ExampleTest.php');
    expect($location->line)->toBeNull();
    expect($location->endLine)->toBeNull();
    expect($location->methodName)->toBeNull();
});

it('creates TestLocation with all fields', function () {
    $location = new TestLocation(
        file: 'tests/ExampleTest.php',
        line: 10,
        endLine: 25,
        methodName: 'test_example'
    );
    
    expect($location->file)->toBe('tests/ExampleTest.php');
    expect($location->line)->toBe(10);
    expect($location->endLine)->toBe(25);
    expect($location->methodName)->toBe('test_example');
});

it('converts TestLocation to array with minimal fields', function () {
    $location = new TestLocation(file: 'tests/ExampleTest.php');
    $array = $location->toArray();
    
    expect($array)->toBe(['file' => 'tests/ExampleTest.php']);
});

it('converts TestLocation to array with all fields', function () {
    $location = new TestLocation(
        file: 'tests/ExampleTest.php',
        line: 10,
        endLine: 25,
        methodName: 'test_example'
    );
    $array = $location->toArray();
    
    expect($array)->toBe([
        'file' => 'tests/ExampleTest.php',
        'line' => 10,
        'endLine' => 25,
        'methodName' => 'test_example',
    ]);
});

// =============================================================================
// TestReference DTO Tests
// =============================================================================

it('creates TestReference with minimal fields', function () {
    $reference = new TestReference(type: 'issue', value: '123');
    
    expect($reference->type)->toBe('issue');
    expect($reference->value)->toBe('123');
    expect($reference->label)->toBeNull();
    expect($reference->url)->toBeNull();
});

it('creates TestReference with all fields', function () {
    $reference = new TestReference(
        type: 'issue',
        value: '123',
        label: 'Bug Fix',
        url: 'https://github.com/user/repo/issues/123'
    );
    
    expect($reference->type)->toBe('issue');
    expect($reference->value)->toBe('123');
    expect($reference->label)->toBe('Bug Fix');
    expect($reference->url)->toBe('https://github.com/user/repo/issues/123');
});

it('converts TestReference to array with minimal fields', function () {
    $reference = new TestReference(type: 'issue', value: '123');
    $array = $reference->toArray();
    
    expect($array)->toBe([
        'type' => 'issue',
        'value' => '123',
    ]);
});

it('converts TestReference to array with all fields', function () {
    $reference = new TestReference(
        type: 'issue',
        value: '123',
        label: 'Bug Fix',
        url: 'https://github.com/user/repo/issues/123'
    );
    $array = $reference->toArray();
    
    expect($array)->toBe([
        'type' => 'issue',
        'value' => '123',
        'label' => 'Bug Fix',
        'url' => 'https://github.com/user/repo/issues/123',
    ]);
});

// =============================================================================
// TestMetadata DTO Tests
// =============================================================================

it('creates TestMetadata with no fields', function () {
    $metadata = new TestMetadata();
    
    expect($metadata->notes)->toBeNull();
    expect($metadata->tags)->toBeNull();
    expect($metadata->references)->toBeNull();
    expect($metadata->custom)->toBeNull();
});

it('creates TestMetadata with all fields', function () {
    $references = [
        new TestReference(type: 'issue', value: '123'),
    ];
    $custom = collect(['class' => 'TestClass']);
    
    $metadata = new TestMetadata(
        notes: 'Test notes',
        tags: ['feature', 'unit'],
        references: $references,
        custom: $custom,
    );
    
    expect($metadata->notes)->toBe('Test notes');
    expect($metadata->tags)->toBe(['feature', 'unit']);
    expect($metadata->references)->toBe($references);
    expect($metadata->custom)->toBe($custom);
});

it('converts TestMetadata to array with no fields', function () {
    $metadata = new TestMetadata();
    $array = $metadata->toArray();
    
    expect($array)->toBe([]);
});

it('converts TestMetadata to array with notes only', function () {
    $metadata = new TestMetadata(notes: 'Test notes');
    $array = $metadata->toArray();
    
    expect($array)->toBe(['notes' => 'Test notes']);
});

it('converts TestMetadata to array with tags only', function () {
    $metadata = new TestMetadata(tags: ['feature', 'unit']);
    $array = $metadata->toArray();
    
    expect($array)->toBe(['tags' => ['feature', 'unit']]);
});

it('converts TestMetadata to array with references', function () {
    $references = [
        new TestReference(type: 'issue', value: '123'),
        new TestReference(type: 'pr', value: '456'),
    ];
    $metadata = new TestMetadata(references: $references);
    $array = $metadata->toArray();
    
    expect($array)->toHaveKey('references');
    expect($array['references'])->toBeArray();
    expect($array['references'])->toHaveCount(2);
});

it('converts TestMetadata to array with custom data', function () {
    $custom = collect(['class' => 'TestClass', 'traits' => ['TraitA']]);
    $metadata = new TestMetadata(custom: $custom);
    $array = $metadata->toArray();
    
    expect($array)->toHaveKey('custom');
    expect($array['custom'])->toBe(['class' => 'TestClass', 'traits' => ['TraitA']]);
});

// =============================================================================
// Test DTO Tests
// =============================================================================

it('creates Test with minimal fields', function () {
    $location = new TestLocation(file: 'tests/ExampleTest.php');
    $test = new Test(
        description: 'it does something',
        location: $location,
    );
    
    expect($test->description)->toBe('it does something');
    expect($test->location)->toBe($location);
    expect($test->caseStatus)->toBe('active');
    expect($test->executionStatus)->toBeNull();
    expect($test->metadata)->toBeNull();
    expect($test->id)->toBeNull();
});

it('creates Test with all fields', function () {
    $location = new TestLocation(
        file: 'tests/ExampleTest.php',
        line: 10,
    );
    $metadata = new TestMetadata(notes: 'Test notes');
    
    $test = new Test(
        description: 'it does something',
        location: $location,
        caseStatus: 'todo',
        executionStatus: 'passing',
        metadata: $metadata,
        id: 'TEST-12345678',
    );
    
    expect($test->description)->toBe('it does something');
    expect($test->location)->toBe($location);
    expect($test->caseStatus)->toBe('todo');
    expect($test->executionStatus)->toBe('passing');
    expect($test->metadata)->toBe($metadata);
    expect($test->id)->toBe('TEST-12345678');
});

it('converts Test to array with minimal fields', function () {
    $location = new TestLocation(file: 'tests/ExampleTest.php');
    $test = new Test(
        description: 'it does something',
        location: $location,
    );
    $array = $test->toArray();
    
    expect($array)->toHaveKey('description', 'it does something');
    expect($array)->toHaveKey('caseStatus', 'active');
    expect($array)->toHaveKey('location');
    expect($array['location'])->toBe(['file' => 'tests/ExampleTest.php']);
    expect($array)->not->toHaveKey('executionStatus');
    expect($array)->not->toHaveKey('metadata');
    expect($array)->not->toHaveKey('id');
});

it('converts Test to array with all fields', function () {
    $location = new TestLocation(
        file: 'tests/ExampleTest.php',
        line: 10,
    );
    $metadata = new TestMetadata(notes: 'Test notes');
    
    $test = new Test(
        description: 'it does something',
        location: $location,
        caseStatus: 'todo',
        executionStatus: 'passing',
        metadata: $metadata,
        id: 'TEST-12345678',
    );
    $array = $test->toArray();
    
    expect($array['description'])->toBe('it does something');
    expect($array['caseStatus'])->toBe('todo');
    expect($array['executionStatus'])->toBe('passing');
    expect($array['id'])->toBe('TEST-12345678');
    expect($array)->toHaveKey('location');
    expect($array)->toHaveKey('metadata');
});

// =============================================================================
// Test ID Generation Tests
// =============================================================================

it('generates deterministic test id', function () {
    $id1 = Test::generateId('tests/Example.php', 'it does something');
    $id2 = Test::generateId('tests/Example.php', 'it does something');
    
    expect($id1)->toBe($id2);
    expect($id1)->toStartWith('TEST-');
});

it('generates different ids for different descriptions', function () {
    $id1 = Test::generateId('tests/Example.php', 'it does something');
    $id2 = Test::generateId('tests/Example.php', 'it does something else');
    
    expect($id1)->not->toBe($id2);
});

it('generates different ids for different files', function () {
    $id1 = Test::generateId('tests/Example1.php', 'it does something');
    $id2 = Test::generateId('tests/Example2.php', 'it does something');
    
    expect($id1)->not->toBe($id2);
});

it('generates id in correct format', function () {
    $id = Test::generateId('tests/Example.php', 'it does something');
    
    expect($id)->toMatch('/^TEST-[a-f0-9]{8}$/');
});

it('generates id with case insensitive file normalization', function () {
    $id1 = Test::generateId('tests/Example.php', 'it does something');
    $id2 = Test::generateId('TESTS/EXAMPLE.PHP', 'it does something');
    
    expect($id1)->toBe($id2);
});

it('generates id with whitespace normalization in description', function () {
    $id1 = Test::generateId('tests/Example.php', 'it does   something');
    $id2 = Test::generateId('tests/Example.php', 'it does something');
    
    expect($id1)->toBe($id2);
});
