<?php

namespace Mateffy\Introspect\Commands;

use Illuminate\Routing\Route;
use Mateffy\Introspect\Facades\Introspect;
use Mateffy\Introspect\Query\Contracts\RouteQueryInterface;

class IntrospectRoutesCommand extends IntrospectCommand
{
    protected $signature = 'introspect:routes
                            {--name= : Filter by route name (supports wildcards)}
                            {--name-not= : Exclude routes matching pattern}
                            {--name-starts= : Name starts with text}
                            {--name-ends= : Name ends with text}
                            {--name-contains= : Name contains text}
                            {--path= : Filter by path (supports wildcards)}
                            {--path-not= : Exclude paths matching pattern}
                            {--path-starts= : Path starts with text}
                            {--path-ends= : Path ends with text}
                            {--path-contains= : Path contains text}
                            {--controller= : Filter by controller class}
                            {--controller-method= : Filter by controller method}
                            {--middleware= : Filter by middleware}
                            {--middleware-all= : Filter by all middlewares (comma-separated)}
                            {--middleware-any= : Filter by any middleware (comma-separated)}
                            {--method= : Filter by HTTP method}
                            {--methods= : Filter by any of these methods (comma-separated)}
                            {--has-param= : Filter by parameter name}
                            {--has-params= : Filter by all these parameters (comma-separated)}
                            {--format=json : Output format (json, jsonl, table, csv, raw)}
                            {--compact : Minified JSON output}
                            {--limit= : Maximum results}
                            {--offset= : Skip N results}
                            {--count : Return count only}
                            {--fields= : Comma-separated fields}
                            {--query= : Query DSL expression}
                            {--query-file= : Path to query file}';

    protected $description = 'Query routes in your application';

    public function handle(): int
    {
        try {
            $query = Introspect::routes();

            // Apply simple filter options
            $this->applySimpleFilters($query);

            // Apply common options (pagination, DSL query)
            $this->configureQuery($query);

            // Execute and output
            $results = $query->get();

            // Transform route objects to arrays for better output
            $transformed = $results->map(fn ($route) => $this->transformRoute($route));

            $this->outputResults($transformed, ['name', 'path', 'methods', 'controller']);

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }

    protected function applySimpleFilters(RouteQueryInterface $query): void
    {
        // Name filters
        if ($this->option('name')) {
            $query->whereNameEquals($this->option('name'));
        }

        if ($this->option('name-not')) {
            $query->whereNameDoesntEqual($this->option('name-not'));
        }

        if ($this->option('name-starts')) {
            $query->whereNameStartsWith($this->option('name-starts'));
        }

        if ($this->option('name-ends')) {
            $query->whereNameEndsWith($this->option('name-ends'));
        }

        if ($this->option('name-contains')) {
            $query->whereNameContains($this->option('name-contains'));
        }

        // Path filters
        if ($this->option('path')) {
            $query->wherePathEquals($this->option('path'));
        }

        if ($this->option('path-not')) {
            $query->wherePathDoesntEqual($this->option('path-not'));
        }

        if ($this->option('path-starts')) {
            $query->wherePathStartsWith($this->option('path-starts'));
        }

        if ($this->option('path-ends')) {
            $query->wherePathEndsWith($this->option('path-ends'));
        }

        if ($this->option('path-contains')) {
            $query->wherePathContains($this->option('path-contains'));
        }

        // Controller filter
        if ($this->option('controller')) {
            $method = $this->option('controller-method');
            $query->whereUsesController($this->option('controller'), $method);
        }

        // Middleware filters
        if ($this->option('middleware')) {
            $query->whereUsesMiddleware($this->option('middleware'));
        }

        if ($this->option('middleware-all')) {
            $middleware = array_map('trim', explode(',', $this->option('middleware-all')));
            $query->whereUsesMiddlewares($middleware, all: true);
        }

        if ($this->option('middleware-any')) {
            $middleware = array_map('trim', explode(',', $this->option('middleware-any')));
            $query->whereUsesMiddlewares($middleware, all: false);
        }

        // Method filters
        if ($this->option('method')) {
            $query->whereUsesMethod($this->option('method'));
        }

        if ($this->option('methods')) {
            $methods = array_map('trim', explode(',', $this->option('methods')));
            $query->whereUsesMethods($methods, all: false);
        }

        // Parameter filters
        if ($this->option('has-param')) {
            $query->whereHasParameter($this->option('has-param'));
        }

        if ($this->option('has-params')) {
            $params = array_map('trim', explode(',', $this->option('has-params')));
            $query->whereHasParameters($params, all: true);
        }
    }

    protected function applyDslQuery($query, string $dsl): void
    {
        $ast = $this->queryParser->parseQuery($dsl);
        $this->queryBuilder->applyToRoutes($query, $ast);
    }

    protected function transformRoute(Route $route): array
    {
        $action = $route->getAction();
        $controller = $action['controller'] ?? null;

        return [
            'name' => $route->getName(),
            'path' => $route->uri(),
            'methods' => $route->methods(),
            'controller' => $controller,
            'middleware' => $route->gatherMiddleware(),
            'parameters' => $route->parameterNames(),
            'domain' => $route->getDomain(),
        ];
    }
}
