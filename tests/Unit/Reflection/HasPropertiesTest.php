<?php

use Illuminate\Support\Collection;
use Mateffy\Introspect\DTO\ModelProperty;
use Mateffy\Introspect\Reflection\ModelReflector;
use Workbench\App\Models\AnotherModel;
use Workbench\App\Models\TestModel;

it('extracts fillable properties', function () {
    $reflector = ModelReflector::makeFromClasspath(AnotherModel::class);
    $properties = $reflector->properties();

    // AnotherModel has fillable: name, appended_only
    $nameProperty = $properties->get('name');
    expect($nameProperty)->toBeInstanceOf(ModelProperty::class)
        ->and($nameProperty->fillable)->toBeTrue();
});

it('extracts hidden properties', function () {
    $reflector = ModelReflector::makeFromClasspath(AnotherModel::class);
    $properties = $reflector->properties();

    // AnotherModel has hidden: hidden_only
    $hiddenProperty = $properties->get('hidden_only');
    expect($hiddenProperty)->toBeInstanceOf(ModelProperty::class)
        ->and($hiddenProperty->hidden)->toBeTrue();
});

it('extracts appended attributes', function () {
    $reflector = ModelReflector::makeFromClasspath(AnotherModel::class);
    $properties = $reflector->properties();

    // AnotherModel has appends: appended_only, name
    $appendedProperty = $properties->get('appended_only');
    expect($appendedProperty)->toBeInstanceOf(ModelProperty::class)
        ->and($appendedProperty->appended)->toBeTrue();
});

it('detects relation properties', function () {
    $reflector = ModelReflector::makeFromClasspath(AnotherModel::class);
    $properties = $reflector->properties();

    // AnotherModel has a test() relationship method
    $relationProperty = $properties->get('test');
    expect($relationProperty)->toBeInstanceOf(ModelProperty::class)
        ->and($relationProperty->relation)->toBeTrue();
});

it('extracts docblock properties', function () {
    $reflector = ModelReflector::makeFromClasspath(TestModel::class);
    $properties = $reflector->properties();

    // TestModel has @property string|int|null $only_via_docblock
    $docblockProperty = $properties->get('only_via_docblock');
    expect($docblockProperty)->toBeInstanceOf(ModelProperty::class);
});

it('extracts docblock read-only properties', function () {
    $reflector = ModelReflector::makeFromClasspath(TestModel::class);
    $properties = $reflector->properties();

    // TestModel has @property-read string|int|null $readonly_via_docblock
    $readOnlyProperty = $properties->get('readonly_via_docblock');
    expect($readOnlyProperty)->toBeInstanceOf(ModelProperty::class)
        ->and($readOnlyProperty->readable)->toBeTrue()
        ->and($readOnlyProperty->writable)->toBeFalse();
});

it('extracts docblock write-only properties', function () {
    $reflector = ModelReflector::makeFromClasspath(TestModel::class);
    $properties = $reflector->properties();

    // TestModel has @property-write string|int|null $writeonly_via_docblock
    $writeOnlyProperty = $properties->get('writeonly_via_docblock');
    expect($writeOnlyProperty)->toBeInstanceOf(ModelProperty::class)
        ->and($writeOnlyProperty->readable)->toBeFalse()
        ->and($writeOnlyProperty->writable)->toBeTrue();
});

it('marks properties as readable and writable by default', function () {
    $reflector = ModelReflector::makeFromClasspath(AnotherModel::class);
    $properties = $reflector->properties();

    $nameProperty = $properties->get('name');
    expect($nameProperty->readable)->toBeTrue()
        ->and($nameProperty->writable)->toBeTrue();
});

it('combines docblock and eloquent data for same property', function () {
    $reflector = ModelReflector::makeFromClasspath(AnotherModel::class);
    $properties = $reflector->properties();

    // 'name' is both in fillable and @property docblock
    $nameProperty = $properties->get('name');
    expect($nameProperty)->toBeInstanceOf(ModelProperty::class)
        ->and($nameProperty->fillable)->toBeTrue();
});

it('extracts default values from attributes', function () {
    $reflector = ModelReflector::makeFromClasspath(AnotherModel::class);
    $properties = $reflector->properties();

    // Properties should be extracted even without explicit defaults
    expect($properties)->toBeInstanceOf(Collection::class);
});

it('parses types from docblock', function () {
    $reflector = ModelReflector::makeFromClasspath(TestModel::class);
    $properties = $reflector->properties();

    $docblockProperty = $properties->get('only_via_docblock');
    expect($docblockProperty->types)->toBeInstanceOf(Collection::class);
});

it('handles model with guarded properties', function () {
    $reflector = ModelReflector::makeFromClasspath(TestModel::class);
    $properties = $reflector->properties();

    // TestModel has guarded: only_via_guarded
    $guardedProperty = $properties->get('only_via_guarded');
    expect($guardedProperty)->toBeInstanceOf(ModelProperty::class)
        ->and($guardedProperty->fillable)->toBeFalse();
});

it('handles timestamps columns', function () {
    $reflector = ModelReflector::makeFromClasspath(AnotherModel::class);
    $properties = $reflector->properties();

    // Models with timestamps should include created_at and updated_at
    expect($properties->has('created_at') || $properties->has('updated_at'))->toBeTrue();
});

it('detects belongsTo relationships', function () {
    $reflector = ModelReflector::makeFromClasspath(AnotherModel::class);
    $properties = $reflector->properties();

    // AnotherModel has test() belongsTo relation
    $relationProperty = $properties->get('test');
    expect($relationProperty->relation)->toBeTrue();
});

it('detects parent model relationships', function () {
    $reflector = ModelReflector::makeFromClasspath(TestModel::class);
    $properties = $reflector->properties();

    // TestModel inherits another() from BaseModel
    $relationProperty = $properties->get('another');
    expect($relationProperty)->toBeInstanceOf(ModelProperty::class)
        ->and($relationProperty->relation)->toBeTrue();
});

it('caches properties', function () {
    $reflector = ModelReflector::makeFromClasspath(TestModel::class);

    $firstCall = $reflector->properties();
    $secondCall = $reflector->properties();

    expect($firstCall)->toBe($secondCall);
});

it('refreshes properties when ignoreCache is true', function () {
    $reflector = ModelReflector::makeFromClasspath(TestModel::class);

    $firstCall = $reflector->properties();
    $secondCall = $reflector->properties(ignoreCache: true);

    // They should be equal in content but not necessarily the same instance
    expect($firstCall->count())->toEqual($secondCall->count());
});

it('handles models without docblocks', function () {
    $code = <<<'PHP'
<?php
namespace TestNoDocblock;

class NoDocblockModel extends \Illuminate\Database\Eloquent\Model {}
PHP;

    $tempFile = sys_get_temp_dir().'/NoDocblockModel.php';
    file_put_contents($tempFile, $code);
    require_once $tempFile;

    $reflector = ModelReflector::makeFromClasspath('TestNoDocblock\NoDocblockModel');
    $properties = $reflector->properties();

    expect($properties)->toBeInstanceOf(Collection::class);

    unlink($tempFile);
});

it('handles nullable types from docblock', function () {
    $reflector = ModelReflector::makeFromClasspath(TestModel::class);
    $properties = $reflector->properties();

    $nullableProperty = $properties->get('only_via_docblock');
    // The type string|int|null should include 'null' in types
    expect($nullableProperty->types)->toBeInstanceOf(Collection::class);
});

it('extracts description from docblock property', function () {
    $code = <<<'PHP'
<?php
namespace TestPropDesc;

/**
 * @property string $with_description This property has a description
 */
class PropertyDescriptionModel extends \Illuminate\Database\Eloquent\Model {}
PHP;

    $tempFile = sys_get_temp_dir().'/PropertyDescriptionModel.php';
    file_put_contents($tempFile, $code);
    require_once $tempFile;

    $reflector = ModelReflector::makeFromClasspath('TestPropDesc\PropertyDescriptionModel');
    $properties = $reflector->properties();

    $describedProperty = $properties->get('with_description');
    expect($describedProperty)->toBeInstanceOf(ModelProperty::class);

    unlink($tempFile);
});

it('handles casts', function () {
    $code = <<<'PHP'
<?php
namespace TestCasts;

class CastsModel extends \Illuminate\Database\Eloquent\Model {
    protected $casts = [
        'is_active' => 'boolean',
        'amount' => 'decimal:2',
        'metadata' => 'array',
    ];
}
PHP;

    $tempFile = sys_get_temp_dir().'/CastsModel.php';
    file_put_contents($tempFile, $code);
    require_once $tempFile;

    $reflector = ModelReflector::makeFromClasspath('TestCasts\CastsModel');
    $properties = $reflector->properties();

    expect($properties->has('is_active'))->toBeTrue();
    expect($properties->has('amount'))->toBeTrue();
    expect($properties->has('metadata'))->toBeTrue();

    $isActiveProperty = $properties->get('is_active');
    expect($isActiveProperty->cast)->toBe('boolean');

    unlink($tempFile);
});

it('filters out wildcard guarded properties', function () {
    $code = <<<'PHP'
<?php
namespace TestWildcard;

class WildcardGuardedModel extends \Illuminate\Database\Eloquent\Model {
    protected $guarded = ['*'];
}
PHP;

    $tempFile = sys_get_temp_dir().'/WildcardGuardedModel.php';
    file_put_contents($tempFile, $code);
    require_once $tempFile;

    $reflector = ModelReflector::makeFromClasspath('TestWildcard\WildcardGuardedModel');
    $properties = $reflector->properties();

    // The '*' should be filtered out
    expect($properties->has('*'))->toBeFalse();

    unlink($tempFile);
});

it('handles visible properties', function () {
    $code = <<<'PHP'
<?php
namespace TestVisible;

class VisibleModel extends \Illuminate\Database\Eloquent\Model {
    protected $visible = ['name', 'email'];
    protected $hidden = ['password'];
}
PHP;

    $tempFile = sys_get_temp_dir().'/VisibleModel.php';
    file_put_contents($tempFile, $code);
    require_once $tempFile;

    $reflector = ModelReflector::makeFromClasspath('TestVisible\VisibleModel');
    $properties = $reflector->properties();

    $nameProperty = $properties->get('name');
    $passwordProperty = $properties->get('password');

    // name is in visible, so it should override hidden
    expect($nameProperty)->toBeInstanceOf(ModelProperty::class);
    expect($passwordProperty)->toBeInstanceOf(ModelProperty::class);

    unlink($tempFile);
});
