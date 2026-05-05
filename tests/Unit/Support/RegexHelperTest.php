<?php

use Mateffy\Introspect\Support\RegexHelper;

it('escapes special regex characters', function () {
    $pattern = 'test.com';
    $escaped = RegexHelper::escape($pattern);

    expect($escaped)->toBe('test\.com');
});

it('converts wildcards to regex patterns', function () {
    $pattern = 'test*';
    $escaped = RegexHelper::escape($pattern);

    expect($escaped)->toBe('test.*');
});

it('matches exact string without wildcards', function () {
    $needle = 'exact-match';
    $texts = ['exact-match', 'other-text'];

    expect(RegexHelper::matches($needle, $texts))->toBeTrue();
});

it('does not match different string without wildcards', function () {
    $needle = 'exact-match';
    $texts = ['different-text', 'other-text'];

    expect(RegexHelper::matches($needle, $texts))->toBeFalse();
});

it('matches wildcard at start', function () {
    $needle = '*test';
    $texts = ['prefix-test', 'mytest', 'test'];

    expect(RegexHelper::matches($needle, $texts))->toBeTrue();
});

it('matches wildcard at end', function () {
    $needle = 'test*';
    $texts = ['test-suffix', 'testing', 'test'];

    expect(RegexHelper::matches($needle, $texts))->toBeTrue();
});

it('matches wildcard in middle', function () {
    $needle = 'test*view';
    $texts = ['test-my-view', 'testview', 'test_any_view'];

    expect(RegexHelper::matches($needle, $texts))->toBeTrue();
});

it('handles multiple wildcards', function () {
    $needle = '*foo*bar*';
    $texts = ['prefix-foo-middle-bar-suffix', 'foobar', 'myfooXbar'];

    expect(RegexHelper::matches($needle, $texts))->toBeTrue();
});

it('returns false for non-matching patterns', function () {
    $needle = 'test*view';
    $texts = ['different-pattern', 'test-only'];

    expect(RegexHelper::matches($needle, $texts))->toBeFalse();
});

it('handles empty needle pattern', function () {
    $needle = '';
    $texts = ['any-text'];

    // Empty pattern does not match (implementation returns false)
    expect(RegexHelper::matches($needle, $texts))->toBeFalse();
});

it('handles empty texts array', function () {
    $needle = 'test';
    $texts = [];

    expect(RegexHelper::matches($needle, $texts))->toBeFalse();
});

it('matches case-insensitively', function () {
    $needle = 'TEST*';
    $texts = ['test-pattern', 'TEST-pattern', 'TestPattern'];

    expect(RegexHelper::matches($needle, $texts))->toBeTrue();
});

it('escapes question mark', function () {
    $pattern = 'test?query';
    $escaped = RegexHelper::escape($pattern);

    expect($escaped)->toBe('test\?query');
});

it('escapes parentheses', function () {
    $pattern = 'test(paren)';
    $escaped = RegexHelper::escape($pattern);

    expect($escaped)->toBe('test\(paren\)');
});

it('escapes square brackets', function () {
    $pattern = 'test[bracket]';
    $escaped = RegexHelper::escape($pattern);

    expect($escaped)->toBe('test\[bracket\]');
});

it('escapes curly braces', function () {
    $pattern = 'test{brace}';
    $escaped = RegexHelper::escape($pattern);

    expect($escaped)->toBe('test\{brace\}');
});

it('escapes plus sign', function () {
    $pattern = 'test+plus';
    $escaped = RegexHelper::escape($pattern);

    expect($escaped)->toBe('test\+plus');
});

it('escapes dollar sign', function () {
    $pattern = 'test$end';
    $escaped = RegexHelper::escape($pattern);

    expect($escaped)->toBe('test\$end');
});

it('escapes caret', function () {
    $pattern = '^start';
    $escaped = RegexHelper::escape($pattern);

    expect($escaped)->toBe('\^start');
});

it('escapes pipe', function () {
    $pattern = 'a|b';
    $escaped = RegexHelper::escape($pattern);

    expect($escaped)->toBe('a\|b');
});

it('escapes forward slash', function () {
    $pattern = 'path/to/file';
    $escaped = RegexHelper::escape($pattern);

    expect($escaped)->toBe('path\/to\/file');
});

it('handles patterns with backslashes', function () {
    $pattern = 'Namespace\\ClassName';
    $escaped = RegexHelper::escape($pattern);

    // Backslashes ARE escaped by the implementation (doubled)
    // Input has 1 backslash, output has 2
    expect($escaped)->toBe('Namespace\\\\ClassName');
});

it('matches first text in array that satisfies pattern', function () {
    $needle = '*test*';
    $texts = ['no-match', 'this-is-test-here', 'also-no-match'];

    expect(RegexHelper::matches($needle, $texts))->toBeTrue();
});

it('handles complex pattern with special chars and wildcards', function () {
    $pattern = 'App\\*Controller';
    $texts = ['App\Http\Controllers\UserController', 'App\Console\Commands\Command'];

    expect(RegexHelper::matches($pattern, $texts))->toBeTrue();
});
