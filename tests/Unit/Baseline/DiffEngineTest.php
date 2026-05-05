<?php

use Mateffy\Introspect\Baseline\Baseline;
use Mateffy\Introspect\Baseline\ColumnSchema;
use Mateffy\Introspect\Baseline\DiffEngine;
use Mateffy\Introspect\Baseline\IndexSchema;
use Mateffy\Introspect\Baseline\MigrationSchema;
use Mateffy\Introspect\Baseline\TableSchema;
use Mateffy\Introspect\DTO\Route;

function makeBaseline(array $routes = [], array $tables = [], array $config = []): Baseline
{
    $routeDtos = collect($routes)->map(fn (array $r) => new Route(
        uri: $r['uri'],
        name: $r['name'] ?? null,
        methods: $r['methods'] ?? ['GET', 'HEAD'],
        controller: $r['controller'] ?? null,
        action: $r['action'] ?? null,
        middlewares: $r['middlewares'] ?? [],
    ));

    $tableSchemas = [];
    foreach ($tables as $tableName => $tableData) {
        $columns = [];
        foreach ($tableData['columns'] ?? [] as $colName => $colData) {
            $columns[$colName] = new ColumnSchema(
                type: $colData['type'] ?? 'string',
                nullable: $colData['nullable'] ?? true,
                default: $colData['default'] ?? null,
                autoincrement: $colData['autoincrement'] ?? false,
                unsigned: $colData['unsigned'] ?? false,
            );
        }

        $indexes = [];
        foreach ($tableData['indexes'] ?? [] as $idxName => $idxData) {
            $indexes[$idxName] = new IndexSchema(
                columns: $idxData['columns'] ?? [],
                unique: $idxData['unique'] ?? false,
                primary: $idxData['primary'] ?? false,
            );
        }

        $tableSchemas[$tableName] = new TableSchema(columns: $columns, indexes: $indexes);
    }

    return new Baseline(
        timestamp: '2025-01-01T00:00:00Z',
        routes: $routeDtos,
        migrations: new MigrationSchema(tables: $tableSchemas),
        config: $config,
    );
}

it('detects no diff between identical baselines', function () {
    $old = makeBaseline(
        routes: [['uri' => '/users', 'name' => 'users.index', 'methods' => ['GET', 'HEAD']]],
        tables: ['users' => ['columns' => ['id' => ['type' => 'integer']]]],
        config: ['app.name' => 'Laravel'],
    );

    $new = makeBaseline(
        routes: [['uri' => '/users', 'name' => 'users.index', 'methods' => ['GET', 'HEAD']]],
        tables: ['users' => ['columns' => ['id' => ['type' => 'integer']]]],
        config: ['app.name' => 'Laravel'],
    );

    $engine = new DiffEngine;
    $diff = $engine->diff($old, $new);

    expect($diff->hasChanges())->toBeFalse()
        ->and($diff->routes->added)->toBeEmpty()
        ->and($diff->routes->removed)->toBeEmpty()
        ->and($diff->routes->changed)->toBeEmpty()
        ->and($diff->migrations->addedTables)->toBeEmpty()
        ->and($diff->migrations->removedTables)->toBeEmpty()
        ->and($diff->migrations->changedTables)->toBeEmpty()
        ->and($diff->config->added)->toBeEmpty()
        ->and($diff->config->removed)->toBeEmpty()
        ->and($diff->config->changed)->toBeEmpty();
});

// --- Route diffs ---

it('detects added routes', function () {
    $old = makeBaseline(routes: [
        ['uri' => '/users', 'name' => 'users.index', 'methods' => ['GET', 'HEAD']],
    ]);

    $new = makeBaseline(routes: [
        ['uri' => '/users', 'name' => 'users.index', 'methods' => ['GET', 'HEAD']],
        ['uri' => '/posts', 'name' => 'posts.index', 'methods' => ['GET', 'HEAD']],
    ]);

    $diff = (new DiffEngine)->diff($old, $new);

    expect($diff->routes->added)->toHaveCount(1)
        ->and($diff->routes->added[0]['uri'])->toBe('/posts')
        ->and($diff->routes->removed)->toBeEmpty();
});

it('detects removed routes', function () {
    $old = makeBaseline(routes: [
        ['uri' => '/users', 'name' => 'users.index', 'methods' => ['GET', 'HEAD']],
        ['uri' => '/posts', 'name' => 'posts.index', 'methods' => ['GET', 'HEAD']],
    ]);

    $new = makeBaseline(routes: [
        ['uri' => '/users', 'name' => 'users.index', 'methods' => ['GET', 'HEAD']],
    ]);

    $diff = (new DiffEngine)->diff($old, $new);

    expect($diff->routes->removed)->toHaveCount(1)
        ->and($diff->routes->removed[0]['uri'])->toBe('/posts')
        ->and($diff->routes->added)->toBeEmpty();
});

it('detects changed route name', function () {
    $old = makeBaseline(routes: [
        ['uri' => '/users', 'name' => 'users.list', 'methods' => ['GET', 'HEAD']],
    ]);

    $new = makeBaseline(routes: [
        ['uri' => '/users', 'name' => 'users.index', 'methods' => ['GET', 'HEAD']],
    ]);

    $diff = (new DiffEngine)->diff($old, $new);

    expect($diff->routes->changed)->toHaveCount(1)
        ->and($diff->routes->changed[0]->name)->toBe('users.index')
        ->and($diff->routes->changed[0]->changes)->toHaveKey('name')
        ->and($diff->routes->changed[0]->changes['name']->from)->toBe('users.list')
        ->and($diff->routes->changed[0]->changes['name']->to)->toBe('users.index');
});

it('detects changed route middleware', function () {
    $old = makeBaseline(routes: [
        ['uri' => '/admin', 'name' => 'admin.dashboard', 'methods' => ['GET', 'HEAD'], 'middlewares' => ['web']],
    ]);

    $new = makeBaseline(routes: [
        ['uri' => '/admin', 'name' => 'admin.dashboard', 'methods' => ['GET', 'HEAD'], 'middlewares' => ['web', 'auth']],
    ]);

    $diff = (new DiffEngine)->diff($old, $new);

    expect($diff->routes->changed)->toHaveCount(1)
        ->and($diff->routes->changed[0]->changes)->toHaveKey('middlewares');
});

it('detects changed route controller', function () {
    $old = makeBaseline(routes: [
        ['uri' => '/users', 'name' => 'users.index', 'methods' => ['GET', 'HEAD'], 'controller' => 'OldController', 'action' => 'index'],
    ]);

    $new = makeBaseline(routes: [
        ['uri' => '/users', 'name' => 'users.index', 'methods' => ['GET', 'HEAD'], 'controller' => 'NewController', 'action' => 'index'],
    ]);

    $diff = (new DiffEngine)->diff($old, $new);

    expect($diff->routes->changed)->toHaveCount(1)
        ->and($diff->routes->changed[0]->changes['controller']->from)->toBe('OldController')
        ->and($diff->routes->changed[0]->changes['controller']->to)->toBe('NewController');
});

it('identifies routes by method+uri key', function () {
    $old = makeBaseline(routes: [
        ['uri' => '/users', 'name' => 'users.index', 'methods' => ['GET', 'HEAD']],
    ]);

    $new = makeBaseline(routes: [
        ['uri' => '/users', 'name' => 'users.index', 'methods' => ['POST']],
    ]);

    $diff = (new DiffEngine)->diff($old, $new);

    // Same URI but different method should be treated as different routes
    expect($diff->routes->added)->toHaveCount(1)
        ->and($diff->routes->removed)->toHaveCount(1);
});

it('diffs routes with same key but different method order', function () {
    $old = makeBaseline(routes: [
        ['uri' => '/users', 'name' => 'users.index', 'methods' => ['HEAD', 'GET']],
    ]);

    $new = makeBaseline(routes: [
        ['uri' => '/users', 'name' => 'users.index', 'methods' => ['GET', 'HEAD']],
    ]);

    $diff = (new DiffEngine)->diff($old, $new);

    // Methods are the same set, just different order - should not be a change
    expect($diff->routes->changed)->toHaveCount(0)
        ->and($diff->routes->added)->toBeEmpty()
        ->and($diff->routes->removed)->toBeEmpty();
});

// --- Migration diffs ---

it('detects added table', function () {
    $old = makeBaseline(tables: [
        'users' => ['columns' => ['id' => ['type' => 'integer']]],
    ]);

    $new = makeBaseline(tables: [
        'users' => ['columns' => ['id' => ['type' => 'integer']]],
        'posts' => ['columns' => ['id' => ['type' => 'integer']]],
    ]);

    $diff = (new DiffEngine)->diff($old, $new);

    expect($diff->migrations->addedTables)->toHaveKey('posts')
        ->and($diff->migrations->removedTables)->toBeEmpty();
});

it('detects removed table', function () {
    $old = makeBaseline(tables: [
        'users' => ['columns' => ['id' => ['type' => 'integer']]],
        'posts' => ['columns' => ['id' => ['type' => 'integer']]],
    ]);

    $new = makeBaseline(tables: [
        'users' => ['columns' => ['id' => ['type' => 'integer']]],
    ]);

    $diff = (new DiffEngine)->diff($old, $new);

    expect($diff->migrations->removedTables)->toHaveKey('posts')
        ->and($diff->migrations->addedTables)->toBeEmpty();
});

it('detects added column', function () {
    $old = makeBaseline(tables: [
        'users' => ['columns' => ['id' => ['type' => 'integer']]],
    ]);

    $new = makeBaseline(tables: [
        'users' => ['columns' => [
            'id' => ['type' => 'integer'],
            'email' => ['type' => 'string'],
        ]],
    ]);

    $diff = (new DiffEngine)->diff($old, $new);

    $usersDiff = $diff->migrations->changedTables['users'];
    expect($usersDiff)->not->toBeNull()
        ->and($usersDiff->addedColumns)->toHaveKey('email')
        ->and($usersDiff->removedColumns)->toBeEmpty();
});

it('detects removed column', function () {
    $old = makeBaseline(tables: [
        'users' => ['columns' => [
            'id' => ['type' => 'integer'],
            'legacy_field' => ['type' => 'string'],
        ]],
    ]);

    $new = makeBaseline(tables: [
        'users' => ['columns' => ['id' => ['type' => 'integer']]],
    ]);

    $diff = (new DiffEngine)->diff($old, $new);

    $usersDiff = $diff->migrations->changedTables['users'];
    expect($usersDiff->removedColumns)->toHaveKey('legacy_field')
        ->and($usersDiff->addedColumns)->toBeEmpty();
});

it('detects changed column type', function () {
    $old = makeBaseline(tables: [
        'users' => ['columns' => [
            'name' => ['type' => 'string'],
        ]],
    ]);

    $new = makeBaseline(tables: [
        'users' => ['columns' => [
            'name' => ['type' => 'text'],
        ]],
    ]);

    $diff = (new DiffEngine)->diff($old, $new);

    $usersDiff = $diff->migrations->changedTables['users'];
    expect($usersDiff->changedColumns)->toHaveKey('name')
        ->and($usersDiff->changedColumns['name']->changes['type']->from)->toBe('string')
        ->and($usersDiff->changedColumns['name']->changes['type']->to)->toBe('text');
});

it('detects changed column nullable', function () {
    $old = makeBaseline(tables: [
        'users' => ['columns' => [
            'email' => ['type' => 'string', 'nullable' => true],
        ]],
    ]);

    $new = makeBaseline(tables: [
        'users' => ['columns' => [
            'email' => ['type' => 'string', 'nullable' => false],
        ]],
    ]);

    $diff = (new DiffEngine)->diff($old, $new);

    $usersDiff = $diff->migrations->changedTables['users'];
    expect($usersDiff->changedColumns['email']->changes['nullable']->from)->toBeTrue()
        ->and($usersDiff->changedColumns['email']->changes['nullable']->to)->toBeFalse();
});

it('detects added index', function () {
    $old = makeBaseline(tables: [
        'users' => ['columns' => ['id' => ['type' => 'integer']], 'indexes' => []],
    ]);

    $new = makeBaseline(tables: [
        'users' => ['columns' => ['id' => ['type' => 'integer']], 'indexes' => [
            'users_email_unique' => ['columns' => ['email'], 'unique' => true],
        ]],
    ]);

    $diff = (new DiffEngine)->diff($old, $new);

    $usersDiff = $diff->migrations->changedTables['users'];
    expect($usersDiff->addedIndexes)->toHaveKey('users_email_unique');
});

it('detects removed index', function () {
    $old = makeBaseline(tables: [
        'users' => ['columns' => ['id' => ['type' => 'integer']], 'indexes' => [
            'users_old_idx' => ['columns' => ['old_col'], 'unique' => false],
        ]],
    ]);

    $new = makeBaseline(tables: [
        'users' => ['columns' => ['id' => ['type' => 'integer']], 'indexes' => []],
    ]);

    $diff = (new DiffEngine)->diff($old, $new);

    $usersDiff = $diff->migrations->changedTables['users'];
    expect($usersDiff->removedIndexes)->toHaveKey('users_old_idx');
});

it('detects changed index', function () {
    $old = makeBaseline(tables: [
        'users' => ['columns' => ['id' => ['type' => 'integer']], 'indexes' => [
            'users_name_idx' => ['columns' => ['name'], 'unique' => false, 'primary' => false],
        ]],
    ]);

    $new = makeBaseline(tables: [
        'users' => ['columns' => ['id' => ['type' => 'integer']], 'indexes' => [
            'users_name_idx' => ['columns' => ['name'], 'unique' => true, 'primary' => false],
        ]],
    ]);

    $diff = (new DiffEngine)->diff($old, $new);

    $usersDiff = $diff->migrations->changedTables['users'];
    expect($usersDiff->changedIndexes)->toHaveKey('users_name_idx');
});

it('returns null table diff when tables are identical', function () {
    $old = makeBaseline(tables: [
        'users' => ['columns' => ['id' => ['type' => 'integer']], 'indexes' => []],
    ]);

    $new = makeBaseline(tables: [
        'users' => ['columns' => ['id' => ['type' => 'integer']], 'indexes' => []],
    ]);

    $diff = (new DiffEngine)->diff($old, $new);

    expect($diff->migrations->changedTables)->toBeEmpty();
});

// --- Config diffs ---

it('detects added config key', function () {
    $old = makeBaseline(config: ['app.name' => 'Laravel']);
    $new = makeBaseline(config: ['app.name' => 'Laravel', 'app.debug' => true]);

    $diff = (new DiffEngine)->diff($old, $new);

    expect($diff->config->added)->toHaveKey('app.debug')
        ->and($diff->config->added['app.debug'])->toBeTrue()
        ->and($diff->config->removed)->toBeEmpty()
        ->and($diff->config->changed)->toBeEmpty();
});

it('detects removed config key', function () {
    $old = makeBaseline(config: ['app.name' => 'Laravel', 'app.debug' => true]);
    $new = makeBaseline(config: ['app.name' => 'Laravel']);

    $diff = (new DiffEngine)->diff($old, $new);

    expect($diff->config->removed)->toHaveKey('app.debug')
        ->and($diff->config->removed['app.debug'])->toBeTrue()
        ->and($diff->config->added)->toBeEmpty()
        ->and($diff->config->changed)->toBeEmpty();
});

it('detects changed config value', function () {
    $old = makeBaseline(config: ['app.name' => 'OldApp', 'app.env' => 'local']);
    $new = makeBaseline(config: ['app.name' => 'NewApp', 'app.env' => 'local']);

    $diff = (new DiffEngine)->diff($old, $new);

    expect($diff->config->changed)->toHaveKey('app.name')
        ->and($diff->config->changed['app.name']->from)->toBe('OldApp')
        ->and($diff->config->changed['app.name']->to)->toBe('NewApp')
        ->and($diff->config->added)->toBeEmpty()
        ->and($diff->config->removed)->toBeEmpty();
});

it('handles complex diff with multiple sections modified', function () {
    $old = makeBaseline(
        routes: [
            ['uri' => '/users', 'name' => 'users.index', 'methods' => ['GET', 'HEAD']],
        ],
        tables: [
            'users' => ['columns' => ['id' => ['type' => 'integer']], 'indexes' => []],
        ],
        config: ['app.name' => 'OldApp'],
    );

    $new = makeBaseline(
        routes: [
            ['uri' => '/users', 'name' => 'users.list', 'methods' => ['GET', 'HEAD']],
            ['uri' => '/posts', 'name' => 'posts.index', 'methods' => ['GET', 'HEAD']],
        ],
        tables: [
            'users' => ['columns' => ['id' => ['type' => 'integer'], 'email' => ['type' => 'string']], 'indexes' => []],
            'posts' => ['columns' => ['id' => ['type' => 'integer']], 'indexes' => []],
        ],
        config: ['app.name' => 'NewApp', 'cache.driver' => 'redis'],
    );

    $engine = new DiffEngine;
    $diff = $engine->diff($old, $new);

    expect($diff->hasChanges())->toBeTrue()
        ->and($diff->routes->added)->toHaveCount(1)
        ->and($diff->routes->changed)->toHaveCount(1)
        ->and($diff->migrations->addedTables)->toHaveKey('posts')
        ->and($diff->migrations->changedTables)->toHaveKey('users')
        ->and($diff->config->added)->toHaveKey('cache.driver')
        ->and($diff->config->changed)->toHaveKey('app.name');
});

it('serializes diff result to JSON and back', function () {
    $old = makeBaseline(
        routes: [['uri' => '/old', 'name' => 'old.route', 'methods' => ['GET']]],
        config: ['key' => 'old_value'],
    );

    $new = makeBaseline(
        routes: [['uri' => '/new', 'name' => 'new.route', 'methods' => ['GET']]],
        config: ['key' => 'new_value'],
    );

    $diff = (new DiffEngine)->diff($old, $new);
    $json = $diff->toJson();
    $decoded = json_decode($json, true);

    expect($decoded)->not->toBeNull()
        ->and($decoded)->toHaveKey('routes')
        ->and($decoded)->toHaveKey('migrations')
        ->and($decoded)->toHaveKey('config')
        ->and($decoded['routes']['removed'])->toHaveCount(1)
        ->and($decoded['routes']['added'])->toHaveCount(1)
        ->and($decoded['config']['changed']['key']['from'])->toBe('old_value')
        ->and($decoded['config']['changed']['key']['to'])->toBe('new_value');
});

it('detects column default value change', function () {
    $old = makeBaseline(tables: [
        'users' => ['columns' => [
            'role' => ['type' => 'string', 'default' => 'user'],
        ]],
    ]);

    $new = makeBaseline(tables: [
        'users' => ['columns' => [
            'role' => ['type' => 'string', 'default' => 'admin'],
        ]],
    ]);

    $diff = (new DiffEngine)->diff($old, $new);

    $usersDiff = $diff->migrations->changedTables['users'];
    expect($usersDiff->changedColumns['role']->changes['default']->from)->toBe('user')
        ->and($usersDiff->changedColumns['role']->changes['default']->to)->toBe('admin');
});

it('detects column autoincrement change', function () {
    $old = makeBaseline(tables: [
        'users' => ['columns' => [
            'id' => ['type' => 'integer', 'autoincrement' => false],
        ]],
    ]);

    $new = makeBaseline(tables: [
        'users' => ['columns' => [
            'id' => ['type' => 'integer', 'autoincrement' => true],
        ]],
    ]);

    $diff = (new DiffEngine)->diff($old, $new);

    $usersDiff = $diff->migrations->changedTables['users'];
    expect($usersDiff->changedColumns['id']->changes['autoincrement']->from)->toBeFalse()
        ->and($usersDiff->changedColumns['id']->changes['autoincrement']->to)->toBeTrue();
});

it('detects column unsigned change', function () {
    $old = makeBaseline(tables: [
        'users' => ['columns' => [
            'id' => ['type' => 'integer', 'unsigned' => false],
        ]],
    ]);

    $new = makeBaseline(tables: [
        'users' => ['columns' => [
            'id' => ['type' => 'integer', 'unsigned' => true],
        ]],
    ]);

    $diff = (new DiffEngine)->diff($old, $new);

    $usersDiff = $diff->migrations->changedTables['users'];
    expect($usersDiff->changedColumns['id']->changes['unsigned']->from)->toBeFalse()
        ->and($usersDiff->changedColumns['id']->changes['unsigned']->to)->toBeTrue();
});
