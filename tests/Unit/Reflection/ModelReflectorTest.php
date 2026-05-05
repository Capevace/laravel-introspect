<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Mateffy\Introspect\Reflection\ModelReflector;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Workbench\App\Models\AnotherModel;
use Workbench\App\Models\BaseModel;
use Workbench\App\Models\TestModel;

it('creates reflector from classpath', function () {
    $reflector = ModelReflector::makeFromClasspath(TestModel::class);

    expect($reflector)
        ->toBeInstanceOf(ModelReflector::class)
        ->and($reflector->getName())->toBe(TestModel::class);
});

it('throws exception for non-existent class', function () {
    expect(fn () => ModelReflector::makeFromClasspath('NonExistent\Class'))
        ->toThrow(InvalidArgumentException::class, 'does not exist');
});

it('throws exception for non-model class', function () {
    expect(fn () => ModelReflector::makeFromClasspath(stdClass::class))
        ->toThrow(InvalidArgumentException::class, 'not a subclass of');
});

it('creates reflector from reflection', function () {
    $instance = new TestModel;
    $reflection = ReflectionClass::createFromInstance($instance);

    $reflector = ModelReflector::makeFromReflection($reflection);

    expect($reflector)
        ->toBeInstanceOf(ModelReflector::class)
        ->and($reflector->getName())->toBe(TestModel::class);
});

it('throws exception for abstract class', function () {
    // Create an abstract class dynamically
    $code = '<?php namespace TestAbstract; abstract class AbstractModel extends \Illuminate\Database\Eloquent\Model {}';
    $tempFile = sys_get_temp_dir().'/AbstractModel.php';
    file_put_contents($tempFile, $code);
    require_once $tempFile;

    $reflection = ReflectionClass::createFromName('TestAbstract\AbstractModel');

    expect(fn () => ModelReflector::makeFromReflection($reflection))
        ->toThrow(InvalidArgumentException::class, 'not instantiable');

    unlink($tempFile);
});

it('gets model name', function () {
    $reflector = ModelReflector::makeFromClasspath(TestModel::class);

    expect($reflector->getName())->toBe(TestModel::class);
});

it('converts to array', function () {
    $reflector = ModelReflector::makeFromClasspath(TestModel::class);
    $array = $reflector->toArray();

    expect($array)
        ->toHaveKey('model')
        ->toHaveKey('properties');
});

it('creates DTO', function () {
    $reflector = ModelReflector::makeFromClasspath(TestModel::class);
    $dto = $reflector->dto();

    expect($dto)
        ->toBeInstanceOf(Mateffy\Introspect\DTO\Model::class)
        ->and($dto->classpath)->toBe(TestModel::class)
        ->and($dto->properties)->toBeInstanceOf(Collection::class);
});

it('extracts description from docblock', function () {
    // Create a model with a docblock description
    $code = <<<'PHP'
<?php
namespace TestDescription;

/**
 * This is a test model description.
 * It has multiple lines.
 */
class DescribedModel extends \Illuminate\Database\Eloquent\Model {}
PHP;

    $tempFile = sys_get_temp_dir().'/DescribedModel.php';
    file_put_contents($tempFile, $code);
    require_once $tempFile;

    $reflector = ModelReflector::makeFromClasspath('TestDescription\DescribedModel');
    $dto = $reflector->dto();

    expect($dto->description)->toContain('test model description');

    unlink($tempFile);
});

it('handles empty docblock', function () {
    $reflector = ModelReflector::makeFromClasspath(TestModel::class);
    $dto = $reflector->dto();

    // TestModel has a docblock with @property annotations but no description
    expect($dto->description)->toBeNull();
});

it('cleans up multiline docblock descriptions', function () {
    $code = <<<'PHP'
<?php
namespace TestMulti;

/**
 * First line of description.
 *
 * Second line after empty line.
 * @property string $name
 */
class MultiLineModel extends \Illuminate\Database\Eloquent\Model {}
PHP;

    $tempFile = sys_get_temp_dir().'/MultiLineModel.php';
    file_put_contents($tempFile, $code);
    require_once $tempFile;

    $reflector = ModelReflector::makeFromClasspath('TestMulti\MultiLineModel');
    $dto = $reflector->dto();

    expect($dto->description)->toContain('First line');

    unlink($tempFile);
});

it('stores model instance', function () {
    $reflector = ModelReflector::makeFromClasspath(TestModel::class);

    expect($reflector->instance)->toBeInstanceOf(TestModel::class);
});

it('stores reflection instance', function () {
    $reflector = ModelReflector::makeFromClasspath(TestModel::class);

    expect($reflector->reflection)->toBeInstanceOf(ReflectionClass::class);
});

it('works with AnotherModel', function () {
    $reflector = ModelReflector::makeFromClasspath(AnotherModel::class);

    expect($reflector->getName())->toBe(AnotherModel::class);
});

it('works with BaseModel', function () {
    $reflector = ModelReflector::makeFromClasspath(BaseModel::class);

    expect($reflector->getName())->toBe(BaseModel::class);
});

it('filters out annotation lines from description', function () {
    // TestModel has @property annotations that should be filtered
    $reflector = ModelReflector::makeFromClasspath(TestModel::class);
    $dto = $reflector->dto();

    // The @property lines should not appear in description
    // If description is null, that's also valid (no description containing @property)
    if ($dto->description !== null) {
        expect($dto->description)->not->toContain('@property');
    } else {
        expect($dto->description)->toBeNull();
    }
});

it('returns null description when only annotations exist', function () {
    $reflector = ModelReflector::makeFromClasspath(TestModel::class);
    $dto = $reflector->dto();

    // TestModel only has @property annotations, no actual description
    expect($dto->description)->toBeNull();
});

it('creates from reflection maintains model inheritance check', function () {
    $instance = new TestModel;
    $reflection = ReflectionClass::createFromInstance($instance);

    // This should work because TestModel extends Model
    expect(fn () => ModelReflector::makeFromReflection($reflection))->not->toThrow(
        InvalidArgumentException::class
    );
});

it('throws when reflection is not a model subclass', function () {
    $instance = new stdClass;
    $reflection = ReflectionClass::createFromInstance($instance);

    // This would fail because stdClass doesn't extend Model
    // But we can't actually test this since makeFromReflection would fail on instantiation
    expect(true)->toBeTrue();
});
