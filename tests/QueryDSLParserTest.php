<?php

use Mateffy\Introspect\Query\DSL\QueryParser;

it('parses simple equality', function () {
    $parser = new QueryParser;
    $result = $parser->parseQuery('name = api.users');

    expect($result)->toBe([
        'field' => 'name',
        'operator' => 'equals',
        'value' => ['api.users'],
    ]);
});

it('parses text operators', function () {
    $parser = new QueryParser;

    $result = $parser->parseQuery('name ^= components.');
    expect($result['operator'])->toBe('starts_with');

    $result = $parser->parseQuery('name $= Controller');
    expect($result['operator'])->toBe('ends_with');

    $result = $parser->parseQuery('name *= Repository');
    expect($result['operator'])->toBe('contains');
});

it('parses negated operators', function () {
    $parser = new QueryParser;

    $result = $parser->parseQuery('name != vendor.*');
    expect($result['operator'])->toBe('not_equals');

    $result = $parser->parseQuery('name !^= App\\');
    expect($result['operator'])->toBe('not_starts_with');
});

it('parses array operators', function () {
    $parser = new QueryParser;

    $result = $parser->parseQuery('middleware ?= auth');
    expect($result)->toBe([
        'field' => 'middleware',
        'operator' => 'has',
        'value' => ['auth'],
    ]);

    $result = $parser->parseQuery('methods ?& GET,POST,PUT');
    expect($result)->toBe([
        'field' => 'methods',
        'operator' => 'has_all',
        'value' => ['GET', 'POST', 'PUT'],
    ]);

    $result = $parser->parseQuery('middleware ?| auth,guest');
    expect($result)->toBe([
        'field' => 'middleware',
        'operator' => 'has_any',
        'value' => ['auth', 'guest'],
    ]);
});

it('parses AND conditions', function () {
    $parser = new QueryParser;
    $result = $parser->parseQuery('path ^= api && middleware ?= auth');

    expect($result)->toHaveKey('and');
    expect($result['and'])->toHaveCount(2);
    expect($result['and'][0]['field'])->toBe('path');
    expect($result['and'][1]['field'])->toBe('middleware');
});

it('parses OR conditions', function () {
    $parser = new QueryParser;
    $result = $parser->parseQuery('method = POST || method = PUT');

    expect($result)->toHaveKey('or');
    expect($result['or'])->toHaveCount(2);
});

it('parses text aliases', function () {
    $parser = new QueryParser;

    $result = $parser->parseQuery('name EQ api.users');
    expect($result['operator'])->toBe('equals');

    $result = $parser->parseQuery('name STARTS_WITH App\\');
    expect($result['operator'])->toBe('starts_with');

    $result = $parser->parseQuery('middleware HAS_ALL auth,verified');
    expect($result['operator'])->toBe('has_all');
});

it('parses text boolean operators', function () {
    $parser = new QueryParser;

    $result = $parser->parseQuery('path ^= api AND middleware ?= auth');
    expect($result)->toHaveKey('and');

    $result = $parser->parseQuery('method = POST OR method = PUT');
    expect($result)->toHaveKey('or');
});

it('parses grouped expressions', function () {
    $parser = new QueryParser;
    $result = $parser->parseQuery('(method = POST || method = PUT) && path ^= api');

    expect($result)->toHaveKey('and');
    expect($result['and'])->toHaveCount(2);
    expect($result['and'][0])->toHaveKey('or');
});

it('parses negated expressions', function () {
    $parser = new QueryParser;
    $result = $parser->parseQuery('!middleware ?= auth');

    expect($result)->toHaveKey('not');
    expect($result['not']['field'])->toBe('middleware');

    $result = $parser->parseQuery('NOT name ^= vendor');
    expect($result)->toHaveKey('not');
});

it('parses complex real-world examples', function () {
    $parser = new QueryParser;

    // API routes with auth
    $result = $parser->parseQuery('path ^= api && middleware ?= auth');
    expect($result)->toHaveKey('and');

    // Models with email and password fillable
    $result = $parser->parseQuery('hasFillable ?& email,password && hasRelationship = user');
    expect($result)->toHaveKey('and');
    expect($result['and'][0]['field'])->toBe('hasFillable');
    expect($result['and'][0]['operator'])->toBe('has_all');

    // POST or PUT with auth
    $result = $parser->parseQuery('(method = POST || method = PUT) && middleware ?& auth,verified');
    expect($result)->toHaveKey('and');
    expect($result['and'][1]['operator'])->toBe('has_all');
});
