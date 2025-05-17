<?php

use Mateffy\Introspect\Facades\Introspect;
use Mateffy\Introspect\LaravelIntrospect;
use Mateffy\Introspect\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

if (! function_exists('introspect')) {
    function introspect(): LaravelIntrospect
    {
        return Introspect::codebase(realpath(__DIR__ . '/..'), ['workbench/app', 'workbench/resources/views']);
    }
}
