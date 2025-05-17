<?php

namespace Mateffy\Introspect\Query\Contracts;

use Mateffy\Introspect\Query\Query;

interface RouteQueryInterface extends Query
{
    // Route names
    public function whereNameStartsWith(string $text): static;

    public function whereNameDoesntStartWith(string $text): static;

    public function whereNameEndsWith(string $text): static;

    public function whereNameDoesntEndWith(string $text): static;

    public function whereNameContains(string $text): static;

    public function whereNameDoesntContain(string $text): static;

    // Route paths
    public function wherePathStartsWith(string $text): static;

    public function wherePathDoesntStartWith(string $text): static;

    // Middlewares
    public function whereUsesMiddleware(string $middleware): static;

    public function whereDoesntUseMiddleware(string $middleware): static;

    public function whereUsesMiddlewares(array $middleware, bool $all = true): static;

    public function whereDoesntUseMiddlewares(array $middleware, bool $all = true): static;

    // Parameters
    public function whereHasParameter(string $parameter): static;

    public function whereDoesntHaveParameter(string $parameter): static;

    public function whereHasParameters(array $parameters, bool $all = true): static;

    public function whereDoesntHaveParameters(array $parameters, bool $all = true): static;

    // Controllers
    public function whereUsesController(string $controller, ?string $method = null): static;

    public function whereDoesntUseController(string $controller, ?string $method = null): static;

    // Methods
    public function whereUsesMethod(string $method): static;

    public function whereDoesntUseMethod(string $method): static;

    public function whereUsesMethods(array $methods, bool $all = true): static;

    public function whereDoesntUseMethods(array $methods, bool $all = true): static;
}
