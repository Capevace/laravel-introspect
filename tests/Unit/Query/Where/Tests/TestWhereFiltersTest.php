<?php

use Mateffy\Introspect\DTO\Test;
use Mateffy\Introspect\DTO\TestLocation;
use Mateffy\Introspect\DTO\TestMetadata;
use Mateffy\Introspect\DTO\TestReference;
use Mateffy\Introspect\Query\Where\TestWhere;
use Mateffy\Introspect\Query\Where\Tests\WhereTestDescriptionContains;
use Mateffy\Introspect\Query\Where\Tests\WhereTestDescriptionEndsWith;
use Mateffy\Introspect\Query\Where\Tests\WhereTestDescriptionEquals;
use Mateffy\Introspect\Query\Where\Tests\WhereTestDescriptionStartsWith;
use Mateffy\Introspect\Query\Where\Tests\WhereTestFileMatches;
use Mateffy\Introspect\Query\Where\Tests\WhereTestHasReferenceType;
use Mateffy\Introspect\Query\Where\Tests\WhereTestHasTag;
use Mateffy\Introspect\Query\Where\Tests\WhereTestStatusEquals;

// Helper function to create a test
function createTest(
    string $description,
    string $file,
    string $caseStatus = 'active',
    ?array $tags = null,
    ?array $references = null,
): Test {
    $location = new TestLocation(file: $file);
    $metadata = null;
    
    if ($tags !== null || $references !== null) {
        $refObjects = null;
        if ($references !== null) {
            $refObjects = array_map(
                fn ($ref) => new TestReference(type: $ref['type'], value: $ref['value']),
                $references
            );
        }
        $metadata = new TestMetadata(tags: $tags, references: $refObjects);
    }
    
    return new Test(
        description: $description,
        location: $location,
        caseStatus: $caseStatus,
        metadata: $metadata,
    );
}

// =============================================================================
// WhereTestFileMatches Tests
// =============================================================================

it('filters test by file matching glob pattern', function () {
    $test = createTest('it works', 'tests/Feature/UserTest.php');
    $where = new WhereTestFileMatches('tests/Feature/*');
    
    expect($where->filter($test))->toBeTrue();
});

it('rejects test when file does not match glob pattern', function () {
    $test = createTest('it works', 'tests/Unit/UserTest.php');
    $where = new WhereTestFileMatches('tests/Feature/*');
    
    expect($where->filter($test))->toBeFalse();
});

it('filters test by file with exact match', function () {
    $test = createTest('it works', 'tests/Feature/UserTest.php');
    $where = new WhereTestFileMatches('tests/Feature/UserTest.php');
    
    expect($where->filter($test))->toBeTrue();
});

it('supports wildcard at start of pattern', function () {
    $test = createTest('it works', 'tests/Feature/UserTest.php');
    $where = new WhereTestFileMatches('*/UserTest.php');
    
    expect($where->filter($test))->toBeTrue();
});

it('supports wildcard in middle of pattern', function () {
    $test = createTest('it works', 'tests/Feature/UserTest.php');
    $where = new WhereTestFileMatches('tests/*/UserTest.php');
    
    expect($where->filter($test))->toBeTrue();
});

it('inverts file filter with not flag', function () {
    $test = createTest('it works', 'tests/Feature/UserTest.php');
    $where = new WhereTestFileMatches('tests/Feature/*', not: true);
    
    expect($where->filter($test))->toBeFalse();
});

it('case insensitive file matching', function () {
    $test = createTest('it works', 'tests/Feature/UserTest.php');
    $where = new WhereTestFileMatches('TESTS/FEATURE/*');
    
    expect($where->filter($test))->toBeTrue();
});

it('file matches implements TestWhere interface', function () {
    $where = new WhereTestFileMatches('tests/*');
    
    expect($where)->toBeInstanceOf(TestWhere::class);
});

// =============================================================================
// WhereTestDescriptionContains Tests
// =============================================================================

it('filters test when description contains text', function () {
    $test = createTest('it creates a user', 'tests/UserTest.php');
    $where = new WhereTestDescriptionContains('user');
    
    expect($where->filter($test))->toBeTrue();
});

it('rejects test when description does not contain text', function () {
    $test = createTest('it creates a user', 'tests/UserTest.php');
    $where = new WhereTestDescriptionContains('admin');
    
    expect($where->filter($test))->toBeFalse();
});

it('case insensitive description matching', function () {
    $test = createTest('It Creates A User', 'tests/UserTest.php');
    $where = new WhereTestDescriptionContains('user');
    
    expect($where->filter($test))->toBeTrue();
});

it('inverts description contains filter with not flag', function () {
    $test = createTest('it creates a user', 'tests/UserTest.php');
    $where = new WhereTestDescriptionContains('user', not: true);
    
    expect($where->filter($test))->toBeFalse();
});

it('matches partial words', function () {
    $test = createTest('it creates a user profile', 'tests/UserTest.php');
    $where = new WhereTestDescriptionContains('user');
    
    expect($where->filter($test))->toBeTrue();
});

it('description contains implements TestWhere interface', function () {
    $where = new WhereTestDescriptionContains('test');
    
    expect($where)->toBeInstanceOf(TestWhere::class);
});

// =============================================================================
// WhereTestDescriptionEquals Tests
// =============================================================================

it('filters test when description equals text', function () {
    $test = createTest('it creates a user', 'tests/UserTest.php');
    $where = new WhereTestDescriptionEquals('it creates a user');
    
    expect($where->filter($test))->toBeTrue();
});

it('rejects test when description does not equal text exactly', function () {
    $test = createTest('it creates a user', 'tests/UserTest.php');
    $where = new WhereTestDescriptionEquals('it creates a user profile');
    
    expect($where->filter($test))->toBeFalse();
});

it('case sensitive description equals matching', function () {
    $test = createTest('It Creates A User', 'tests/UserTest.php');
    $where = new WhereTestDescriptionEquals('it creates a user');
    
    // Note: equals should be case sensitive, so this should fail
    expect($where->filter($test))->toBeFalse();
});

it('inverts description equals filter with not flag', function () {
    $test = createTest('it creates a user', 'tests/UserTest.php');
    $where = new WhereTestDescriptionEquals('it creates a user', not: true);
    
    expect($where->filter($test))->toBeFalse();
});

it('description equals implements TestWhere interface', function () {
    $where = new WhereTestDescriptionEquals('test');
    
    expect($where)->toBeInstanceOf(TestWhere::class);
});

// =============================================================================
// WhereTestDescriptionStartsWith Tests
// =============================================================================

it('filters test when description starts with text', function () {
    $test = createTest('it creates a user', 'tests/UserTest.php');
    $where = new WhereTestDescriptionStartsWith('it creates');
    
    expect($where->filter($test))->toBeTrue();
});

it('rejects test when description does not start with text', function () {
    $test = createTest('the user is created', 'tests/UserTest.php');
    $where = new WhereTestDescriptionStartsWith('it creates');
    
    expect($where->filter($test))->toBeFalse();
});

it('case insensitive starts with matching', function () {
    $test = createTest('IT CREATES A USER', 'tests/UserTest.php');
    $where = new WhereTestDescriptionStartsWith('it creates');
    
    expect($where->filter($test))->toBeTrue();
});

it('inverts starts with filter with not flag', function () {
    $test = createTest('it creates a user', 'tests/UserTest.php');
    $where = new WhereTestDescriptionStartsWith('it creates', not: true);
    
    expect($where->filter($test))->toBeFalse();
});

it('starts with implements TestWhere interface', function () {
    $where = new WhereTestDescriptionStartsWith('it');
    
    expect($where)->toBeInstanceOf(TestWhere::class);
});

// =============================================================================
// WhereTestDescriptionEndsWith Tests
// =============================================================================

it('filters test when description ends with text', function () {
    $test = createTest('it creates a user', 'tests/UserTest.php');
    $where = new WhereTestDescriptionEndsWith('a user');
    
    expect($where->filter($test))->toBeTrue();
});

it('rejects test when description does not end with text', function () {
    $test = createTest('it creates a user profile', 'tests/UserTest.php');
    $where = new WhereTestDescriptionEndsWith('a user');
    
    expect($where->filter($test))->toBeFalse();
});

it('case insensitive ends with matching', function () {
    $test = createTest('IT CREATES A USER', 'tests/UserTest.php');
    $where = new WhereTestDescriptionEndsWith('a user');
    
    expect($where->filter($test))->toBeTrue();
});

it('inverts ends with filter with not flag', function () {
    $test = createTest('it creates a user', 'tests/UserTest.php');
    $where = new WhereTestDescriptionEndsWith('a user', not: true);
    
    expect($where->filter($test))->toBeFalse();
});

it('ends with implements TestWhere interface', function () {
    $where = new WhereTestDescriptionEndsWith('user');
    
    expect($where)->toBeInstanceOf(TestWhere::class);
});

// =============================================================================
// WhereTestStatusEquals Tests
// =============================================================================

it('filters test when status matches', function () {
    $test = createTest('it works', 'tests/Test.php', caseStatus: 'active');
    $where = new WhereTestStatusEquals('active');
    
    expect($where->filter($test))->toBeTrue();
});

it('rejects test when status does not match', function () {
    $test = createTest('it works', 'tests/Test.php', caseStatus: 'todo');
    $where = new WhereTestStatusEquals('active');
    
    expect($where->filter($test))->toBeFalse();
});

it('filters test with todo status', function () {
    $test = createTest('it works', 'tests/Test.php', caseStatus: 'todo');
    $where = new WhereTestStatusEquals('todo');
    
    expect($where->filter($test))->toBeTrue();
});

it('inverts status equals filter with not flag', function () {
    $test = createTest('it works', 'tests/Test.php', caseStatus: 'active');
    $where = new WhereTestStatusEquals('active', not: true);
    
    expect($where->filter($test))->toBeFalse();
});

it('status equals implements TestWhere interface', function () {
    $where = new WhereTestStatusEquals('active');
    
    expect($where)->toBeInstanceOf(TestWhere::class);
});

// =============================================================================
// WhereTestHasTag Tests
// =============================================================================

it('filters test when it has the specified tag', function () {
    $test = createTest('it works', 'tests/Test.php', tags: ['feature', 'unit']);
    $where = new WhereTestHasTag('feature');
    
    expect($where->filter($test))->toBeTrue();
});

it('rejects test when it does not have the specified tag', function () {
    $test = createTest('it works', 'tests/Test.php', tags: ['feature', 'unit']);
    $where = new WhereTestHasTag('integration');
    
    expect($where->filter($test))->toBeFalse();
});

it('rejects test when it has no tags', function () {
    $test = createTest('it works', 'tests/Test.php');
    $where = new WhereTestHasTag('feature');
    
    expect($where->filter($test))->toBeFalse();
});

it('inverts has tag filter with not flag', function () {
    $test = createTest('it works', 'tests/Test.php', tags: ['feature']);
    $where = new WhereTestHasTag('feature', not: true);
    
    expect($where->filter($test))->toBeFalse();
});

it('has tag implements TestWhere interface', function () {
    $where = new WhereTestHasTag('feature');
    
    expect($where)->toBeInstanceOf(TestWhere::class);
});

// =============================================================================
// WhereTestHasReferenceType Tests
// =============================================================================

it('filters test when it has the specified reference type', function () {
    $test = createTest('it works', 'tests/Test.php', references: [
        ['type' => 'issue', 'value' => '123'],
        ['type' => 'pr', 'value' => '456'],
    ]);
    $where = new WhereTestHasReferenceType('issue');
    
    expect($where->filter($test))->toBeTrue();
});

it('rejects test when it does not have the specified reference type', function () {
    $test = createTest('it works', 'tests/Test.php', references: [
        ['type' => 'issue', 'value' => '123'],
    ]);
    $where = new WhereTestHasReferenceType('pr');
    
    expect($where->filter($test))->toBeFalse();
});

it('rejects test when it has no references', function () {
    $test = createTest('it works', 'tests/Test.php');
    $where = new WhereTestHasReferenceType('issue');
    
    expect($where->filter($test))->toBeFalse();
});

it('rejects test when it has no metadata', function () {
    $test = createTest('it works', 'tests/Test.php');
    $where = new WhereTestHasReferenceType('issue');
    
    expect($where->filter($test))->toBeFalse();
});

it('inverts has reference type filter with not flag', function () {
    $test = createTest('it works', 'tests/Test.php', references: [
        ['type' => 'issue', 'value' => '123'],
    ]);
    $where = new WhereTestHasReferenceType('issue', not: true);
    
    expect($where->filter($test))->toBeFalse();
});

it('has reference type implements TestWhere interface', function () {
    $where = new WhereTestHasReferenceType('issue');
    
    expect($where)->toBeInstanceOf(TestWhere::class);
});
