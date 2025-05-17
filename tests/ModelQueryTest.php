<?php

use Mateffy\Introspect\Query\Contracts\ModelQueryInterface;

$totalModels = 4;

it('can query all models', function () use ($totalModels) {
    $classes = introspect()
        ->models()
        ->get();

    file_put_contents('/Users/mat/Downloads/models.txt', json_encode($classes->toArray(), JSON_PRETTY_PRINT));

    expect($classes)->toHaveCount($totalModels);
});

it('can offset models', function () use ($totalModels) {
    $models = introspect()
        ->models()
        ->offset(2)
        ->get();

    expect($models->count())->toEqual($totalModels - 2);
});

it('can limit models', function () use ($totalModels) {
    $models = introspect()
        ->models()
        ->limit(2)
        ->get();

    expect($models->count())->toEqual(2);
});

it('can query by properties', function (string|array $property, string $method, int $count, bool $all = true) use ($totalModels) {
    $query = introspect()->models();
    $oppositeQuery = introspect()->models();

    $properties = is_array($property) ? $property : [$property];
    $propertiesAsText = implode(', ', $properties);

    $and_or = ($all ? 'and' : 'or');
    $or_and = ($all ? 'or' : 'and');

    $models = (match ($method) {
        'hasProperty' => $query->whereHasProperties($properties, all: $all),
        'hasFillable' => $query->whereHasFillableProperties($properties, all: $all),
        'hasHidden' => $query->whereHasHiddenProperties($properties, all: $all),
        'hasAppended' => $query->whereHasAppendedProperties($properties, all: $all),
        'hasReadable' => $query->whereHasReadableProperties($properties, all: $all),
        'hasWritable' => $query->whereHasWritableProperties($properties, all: $all),
        'hasRelation' => $query->{($all ? 'and' : 'or')}(fn (ModelQueryInterface $subquery) => collect($properties)->each(fn ($property) => $subquery->whereHasRelationship($property))),
        default => throw new InvalidArgumentException("Invalid method $method"),
    })->get();

    $oppositeModels = (match ($method) {
        'hasProperty' => $oppositeQuery->whereDoesntHaveProperties($properties, all: $all),
        'hasFillable' => $oppositeQuery->whereDoesntHaveFillableProperties($properties, all: $all),
        'hasHidden' => $oppositeQuery->whereDoesntHaveHiddenProperties($properties, all: $all),
        'hasAppended' => $oppositeQuery->whereDoesntHaveAppendedProperties($properties, all: $all),
        'hasReadable' => $oppositeQuery->whereDoesntHaveReadableProperties($properties, all: $all),
        'hasWritable' => $oppositeQuery->whereDoesntHaveWritableProperties($properties, all: $all),
        'hasRelation' => $oppositeQuery->{$or_and}(fn (ModelQueryInterface $subquery) => collect($properties)->each(fn ($property) => $subquery->whereDoesntHaveRelationship($property))),
        default => throw new InvalidArgumentException("Invalid method $method"),
    })->get();

    $oppositeCount = $totalModels - $count;

    expect($models)
        ->toHaveCount($count, "Expected $method $propertiesAsText to return $count models, but got {$models->count()}")
        ->and($oppositeModels)
        ->toHaveCount($totalModels - $count, "Expected opposite $method $propertiesAsText to return {$oppositeCount} models, but got {$oppositeModels->count()}");
})
    ->with([
        // has property
        ['name', 'hasProperty', 2],
        [['name', 'email'], 'hasProperty', 1],
        ['nonExistant', 'hasProperty', 0],
        [['name', 'nonExistant'], 'hasProperty', 0],
        ['nested_not_overridden', 'hasProperty', 2],
        [['name', 'email'], 'hasProperty', 2, false],

        // Fillable
        ['name', 'hasFillable', 2],
        ['name', 'hasFillable', 2],
        [['name', 'email'], 'hasFillable', 2, false],

        // Hidden
        ['password', 'hasHidden', 1],
        [['password', 'hidden_only'], 'hasHidden', 2, false],

        // Appended
        ['appended_only', 'hasAppended', 1],
        [['appended_only', 'name'], 'hasAppended', 1],
        [['appended_only', 'password'], 'hasAppended', 0],
        [['appended_only', 'email'], 'hasAppended', 2, false],
        [['appended_only', 'email'], 'hasAppended', 0],

        // Docblock only
        ['only_via_docblock', 'hasProperty', 1],
        [['only_via_docblock', 'only_via_guarded'], 'hasProperty', 1],
        [['only_via_docblock', 'email'], 'hasProperty', 0],
        [['only_via_docblock', 'email'], 'hasProperty', 2, false],

        // Readable and writable
        ['readonly_via_docblock', 'hasReadable', 1],
        ['readonly_via_docblock', 'hasWritable', 0],
        ['writeonly_via_docblock', 'hasReadable', 0],
        ['writeonly_via_docblock', 'hasWritable', 1],
        ['name', 'hasReadable', 2],
        ['name', 'hasWritable', 2],
        [['readonly_via_docblock', 'email'], 'hasReadable', 0],
        [['readonly_via_docblock', 'email'], 'hasReadable', 2, false],
        [['writeonly_via_docblock', 'email'], 'hasWritable', 0],
        [['writeonly_via_docblock', 'email'], 'hasWritable', 2, false],
        [['readonly_via_docblock', 'writeonly_via_docblock'], 'hasReadable', 0],
        [['readonly_via_docblock', 'writeonly_via_docblock'], 'hasReadable', 1, false],
        [['readonly_via_docblock', 'writeonly_via_docblock'], 'hasWritable', 0],
        [['readonly_via_docblock', 'writeonly_via_docblock'], 'hasWritable', 1, false],

        // Relationship
        ['test', 'hasRelation', 1],
        ['another', 'hasRelation', 3],
        ['another2', 'hasRelation', 1],
        [['non_existant', 'another2'], 'hasRelation', 0],
        [['another', 'another2'], 'hasRelation', 1],
        [['another', 'another2'], 'hasRelation', 3, false],
    ]);

