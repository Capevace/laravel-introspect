<?php

use Mateffy\Introspect\Baseline\Baseline;
use Mateffy\Introspect\Baseline\ColumnSchema;
use Mateffy\Introspect\Baseline\IndexSchema;
use Mateffy\Introspect\Baseline\MigrationSchema;
use Mateffy\Introspect\Baseline\TableSchema;
use Mateffy\Introspect\DTO\Route;

it('creates a Baseline and converts to array', function () {
    $routes = collect([
        new Route(
            uri: '/users',
            name: 'users.index',
            methods: ['GET', 'HEAD'],
            controller: 'UserController',
            action: 'index',
            middlewares: ['web'],
        ),
    ]);

    $usersTable = new TableSchema(
        columns: [
            'id' => new ColumnSchema('integer', false, null, true, false),
            'name' => new ColumnSchema('string', false, null, false, false),
        ],
        indexes: [
            'users_pkey' => new IndexSchema(['id'], true, true),
        ],
    );

    $migrations = new MigrationSchema(tables: ['users' => $usersTable]);
    $config = ['app.name' => 'Laravel', 'app.env' => 'local'];

    $baseline = new Baseline(
        timestamp: '2025-01-01T00:00:00Z',
        routes: $routes,
        migrations: $migrations,
        config: $config,
    );

    $arr = $baseline->toArray();

    expect($arr)->toHaveKey('timestamp', '2025-01-01T00:00:00Z')
        ->and($arr)->toHaveKey('routes')
        ->and($arr)->toHaveKey('migrations')
        ->and($arr)->toHaveKey('config')
        ->and($arr['routes'])->toHaveCount(1)
        ->and($arr['routes'][0]['uri'])->toBe('/users')
        ->and($arr['routes'][0]['name'])->toBe('users.index')
        ->and($arr['migrations']['tables'])->toHaveKey('users')
        ->and($arr['config']['app.name'])->toBe('Laravel');
});

it('serializes Baseline to JSON', function () {
    $baseline = new Baseline(
        timestamp: '2025-01-01T00:00:00Z',
        routes: collect([]),
        migrations: new MigrationSchema(tables: []),
        config: ['app.name' => 'MyApp'],
    );

    $json = $baseline->toJson();

    $decoded = json_decode($json, true);
    expect($decoded)->not->toBeNull()
        ->and($decoded['timestamp'])->toBe('2025-01-01T00:00:00Z')
        ->and($decoded['config']['app.name'])->toBe('MyApp');
});

it('serializes Baseline to compact JSON', function () {
    $baseline = new Baseline(
        timestamp: '2025-01-01T00:00:00Z',
        routes: collect([]),
        migrations: new MigrationSchema(tables: []),
        config: [],
    );

    $json = $baseline->toJson(JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    expect(str_contains($json, "\n"))->toBeFalse('Compact JSON should not contain newlines');
});

it('deserializes Baseline from array', function () {
    $data = [
        'timestamp' => '2025-01-01T00:00:00Z',
        'routes' => [
            [
                'uri' => '/api/users',
                'name' => 'api.users.index',
                'methods' => ['GET', 'HEAD'],
                'controller' => 'Api\\UserController',
                'action' => 'index',
                'middlewares' => ['api', 'auth'],
            ],
        ],
        'migrations' => [
            'tables' => [
                'users' => [
                    'columns' => [
                        'id' => [
                            'type' => 'integer',
                            'nullable' => false,
                            'default' => null,
                            'autoincrement' => true,
                            'unsigned' => true,
                        ],
                        'email' => [
                            'type' => 'string',
                            'nullable' => false,
                            'default' => null,
                            'autoincrement' => false,
                            'unsigned' => false,
                        ],
                    ],
                    'indexes' => [
                        'users_pkey' => [
                            'columns' => ['id'],
                            'unique' => true,
                            'primary' => true,
                        ],
                        'users_email_unique' => [
                            'columns' => ['email'],
                            'unique' => true,
                            'primary' => false,
                        ],
                    ],
                ],
            ],
        ],
        'config' => [
            'app.name' => 'TestApp',
            'app.env' => 'testing',
        ],
    ];

    $baseline = Baseline::fromArray($data);

    expect($baseline->timestamp)->toBe('2025-01-01T00:00:00Z')
        ->and($baseline->routes)->toHaveCount(1)
        ->and($baseline->routes->first()->uri)->toBe('/api/users')
        ->and($baseline->routes->first()->name)->toBe('api.users.index')
        ->and($baseline->routes->first()->methods)->toBe(['GET', 'HEAD'])
        ->and($baseline->routes->first()->controller)->toBe('Api\\UserController')
        ->and($baseline->routes->first()->action)->toBe('index')
        ->and($baseline->routes->first()->middlewares)->toBe(['api', 'auth'])
        ->and($baseline->migrations->tables)->toHaveKey('users')
        ->and($baseline->migrations->tables['users']->columns)->toHaveKey('id')
        ->and($baseline->migrations->tables['users']->columns['id']->type)->toBe('integer')
        ->and($baseline->migrations->tables['users']->columns['id']->autoincrement)->toBeTrue()
        ->and($baseline->migrations->tables['users']->columns['id']->nullable)->toBeFalse()
        ->and($baseline->migrations->tables['users']->indexes)->toHaveKey('users_pkey')
        ->and($baseline->migrations->tables['users']->indexes['users_pkey']->primary)->toBeTrue()
        ->and($baseline->config['app.name'])->toBe('TestApp')
        ->and($baseline->config['app.env'])->toBe('testing');
});

it('deserializes Baseline from JSON', function () {
    $json = json_encode([
        'timestamp' => '2025-01-01T00:00:00Z',
        'routes' => [
            [
                'uri' => '/posts',
                'name' => 'posts.index',
                'methods' => ['GET'],
                'controller' => null,
                'action' => null,
                'middlewares' => [],
            ],
        ],
        'migrations' => [
            'tables' => [],
        ],
        'config' => [],
    ]);

    $baseline = Baseline::fromJson($json);

    expect($baseline->routes)->toHaveCount(1)
        ->and($baseline->routes->first()->uri)->toBe('/posts')
        ->and($baseline->migrations->tables)->toBeEmpty();
});

it('round-trips Baseline through JSON', function () {
    $routes = collect([
        new Route(
            uri: '/api/posts/{id}',
            name: 'api.posts.show',
            methods: ['GET', 'HEAD'],
            controller: 'PostController',
            action: 'show',
            middlewares: ['auth', 'api'],
        ),
    ]);

    $originalBaseline = new Baseline(
        timestamp: '2025-06-15T12:00:00Z',
        routes: $routes,
        migrations: new MigrationSchema(tables: [
            'posts' => new TableSchema(
                columns: [
                    'id' => new ColumnSchema('integer', false, null, true, false),
                    'title' => new ColumnSchema('string', false, null, false, false),
                    'body' => new ColumnSchema('text', true, null, false, false),
                ],
                indexes: [
                    'posts_pkey' => new IndexSchema(['id'], true, true),
                ],
            ),
        ]),
        config: ['app.name' => 'BlogApp', 'app.debug' => true],
    );

    $json = $originalBaseline->toJson();
    $restored = Baseline::fromJson($json);

    expect($restored->timestamp)->toBe('2025-06-15T12:00:00Z')
        ->and($restored->routes)->toHaveCount(1)
        ->and($restored->routes->first()->uri)->toBe('/api/posts/{id}')
        ->and($restored->routes->first()->name)->toBe('api.posts.show')
        ->and($restored->routes->first()->methods)->toBe(['GET', 'HEAD'])
        ->and($restored->routes->first()->middlewares)->toBe(['auth', 'api'])
        ->and($restored->migrations->tables)->toHaveKey('posts')
        ->and($restored->migrations->tables['posts']->columns)->toHaveKey('id')
        ->and($restored->migrations->tables['posts']->columns['id']->type)->toBe('integer')
        ->and($restored->migrations->tables['posts']->columns['id']->autoincrement)->toBeTrue()
        ->and($restored->migrations->tables['posts']->columns)->toHaveKey('title')
        ->and($restored->migrations->tables['posts']->columns)->toHaveKey('body')
        ->and($restored->migrations->tables['posts']->columns['body']->nullable)->toBeTrue()
        ->and($restored->migrations->tables['posts']->indexes['posts_pkey']->primary)->toBeTrue()
        ->and($restored->config['app.name'])->toBe('BlogApp')
        ->and($restored->config['app.debug'])->toBeTrue()
        ->and($restored->config)->toBe($originalBaseline->config);
});

it('handles empty Baseline', function () {
    $baseline = new Baseline(
        timestamp: '2025-01-01T00:00:00Z',
        routes: collect([]),
        migrations: new MigrationSchema(tables: []),
        config: [],
    );

    $arr = $baseline->toArray();

    expect($arr['routes'])->toBeEmpty()
        ->and($arr['migrations']['tables'])->toBeEmpty()
        ->and($arr['config'])->toBeEmpty();
});

it('deserializes Baseline with missing optional keys', function () {
    $data = [
        'timestamp' => '2025-01-01T00:00:00Z',
        'routes' => [],
        'migrations' => ['tables' => []],
        'config' => [],
    ];

    $baseline = Baseline::fromArray($data);

    expect($baseline->routes)->toBeEmpty()
        ->and($baseline->migrations->tables)->toBeEmpty()
        ->and($baseline->config)->toBeEmpty();
});

it('handles default values when deserializing columns', function () {
    $data = [
        'timestamp' => '2025-01-01T00:00:00Z',
        'routes' => [],
        'migrations' => [
            'tables' => [
                'test' => [
                    'columns' => [
                        'col' => [
                            'type' => 'string',
                        ],
                    ],
                    'indexes' => [],
                ],
            ],
        ],
        'config' => [],
    ];

    $baseline = Baseline::fromArray($data);

    $col = $baseline->migrations->tables['test']->columns['col'];
    expect($col->type)->toBe('string')
        ->and($col->nullable)->toBeTrue() // default
        ->and($col->default)->toBeNull()
        ->and($col->autoincrement)->toBeFalse()
        ->and($col->unsigned)->toBeFalse();
});

it('deserializes index with missing keys using defaults', function () {
    $data = [
        'timestamp' => '2025-01-01T00:00:00Z',
        'routes' => [],
        'migrations' => [
            'tables' => [
                'test' => [
                    'columns' => [],
                    'indexes' => [
                        'my_idx' => [
                            'columns' => ['col_a'],
                        ],
                    ],
                ],
            ],
        ],
        'config' => [],
    ];

    $baseline = Baseline::fromArray($data);

    $idx = $baseline->migrations->tables['test']->indexes['my_idx'];
    expect($idx->columns)->toBe(['col_a'])
        ->and($idx->unique)->toBeFalse()
        ->and($idx->primary)->toBeFalse();
});

it('handles route with null controller and action', function () {
    $routes = collect([
        new Route(
            uri: '/closure-route',
            name: null,
            methods: ['GET', 'HEAD'],
            controller: null,
            action: null,
            middlewares: [],
        ),
    ]);

    $baseline = new Baseline(
        timestamp: '2025-01-01T00:00:00Z',
        routes: $routes,
        migrations: new MigrationSchema(tables: []),
        config: [],
    );

    $json = $baseline->toJson();
    $restored = Baseline::fromJson($json);

    expect($restored->routes->first()->controller)->toBeNull()
        ->and($restored->routes->first()->name)->toBeNull()
        ->and($restored->routes->first()->uri)->toBe('/closure-route')
        ->and($restored->routes->first()->methods)->toBe(['GET', 'HEAD']);
});
