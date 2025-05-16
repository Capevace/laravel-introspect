# Introspect for Laravel

A utility library to analyze and pull information from Laravel codebases, querying model, route and more Laravel-specific things directly from your codebase using PHP reflection.
It is targeted for development tools and coding agents for better understanding of the codebase.

> [!WARNING]  
> I'm still finalizing the API and using this documentation for API design.
> Not all methods listed here have been implemented yet and some of them may change until 1.0 release. 

## Who is this for?

Are you building a devtool or other application that needs to introspect a Laravel codebase?
Then this package will make your life a lot easier by providing a fluent API to query models, routes, controllers, views and more.

## Installation

Install the package via composer:

```bash
composer require mateffy/laravel-introspect
```

## Usage

```php
use Mateffy\Introspect\Facades\Introspect;

$models = Introspect::models()->get();
$routes = Introspect::routes()->get();
$controllers = Introspect::controllers()->get();
$configurations = Introspect::config()->get();
$views = Introspect::views()->get();
// and more!
```

## Examples

### Query all models that use a trait
```php
$models = Introspect::models()
    ->whereUses(MyTrait::class)
    ->get();
```

### Query all routes that use a controller
```php
$routes = Introspect::routes()
    ->whereController(MyController::class)
    ->whereMethod('index')
    ->get();
```

### Query all routes that use a specific middleware
```php
$routes = Introspect::routes()
    ->whereMiddleware(MyMiddleware::class)
    ->whereMiddleware('web')
    ->get();
```

### Views

#### Query all views that are used in specific views
```php
$routes = Introspect::views()
    ->whereUsedIn('pages.welcome')
    ->get();
```

#### Query all views that use a specific view
```php
$routes = Introspect::views()
    ->whereUses('components.button')
    ->get();
```

#### Query views by view path
```php
$views = Introspect::views()
    ->whereNameStartsWith('pages.')
    ->get();

$views = Introspect::views()
    ->whereNameEndsWith('button')
    ->get();

$views = Introspect::views()
    ->whereNameContains('button')
    ->get();
```

### Search through your codebase using vector embeddings

Introspect has support for indexing and querying your codebase using vector embeddings. 
It does so using the [LLM Magic](https://github.com/capevace/llm-magic) package, which allows you to use most popular LLMs using your own API keys.

### Limit the results and paginate just like using Eloquent queries
```php
$models = Introspect::models()
    ->limit(10)
    ->offset(20)
    ->get();
```

### Build Queries with JSON instead of code

All the type-safe queries above can also be expressed in JSON format.
This is to make it easier for LLMs to be able to more flexibly query the codebase.

```php
$query = <<<JSON
{
    "type": "models",
    "query": {
        "filters": [
            {
                "type": "whereTrait",
                "value": "MyTrait::class"
            }
        ],
        "limit": 10,
        "offset": 20
    }
}
JSON;

$models = Introspect::query($query)->get();
```


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
