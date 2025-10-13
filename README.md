<img src="./docs/header@2.webp" width="100%">

![Development Tests Status](https://img.shields.io/github/actions/workflow/status/capevace/laravel-introspect/run-tests.yml?label=dev%20tests)
![Latest Version](https://img.shields.io/github/v/tag/capevace/laravel-introspect?label=latest+version)

[//]: # (![Packagist Downloads]&#40;https://img.shields.io/packagist/dt/mateffy/laravel-introspect&#41;)

# Introspect for Laravel

A utility package to analyze Laravel codebases, querying views, models, routes, classes and more directly from your
codebase using a type-safe fluent API.

<br />

- 🔍 Query views, routes, classes and models with a fluent API
- 🔍 Use wildcards (`*`) to match multiple views, routes, classes and models
- 🪄 Parse properties, relationships + their types and more directly from Eloquent model code
- 🤖 (De-)serialize queries to/from JSON (perfect for LLM tool calling)

<br />

| Query      | Available Filters                                                      |
|------------|------------------------------------------------------------------------|
| Views      | name, path, used by view, uses view, extends                           |
| Routes     | name, URI, controller + fn, methods, middleware                        |
| Classes    | name / namespace, extends parent, implements interfaces, uses traits   |
| ⤷ Models   | ... relationships, properties, casts, fillable, hidden, read/writeable |
| ⤷ Commands | ... signature, description (_coming soon_)                             |

> Name and a few other queries even support wildcard queries (e.g. `components.*.paragraph`)

<br />

### Who is this for?

Are you working on a complex refactoring job and need to find all the places where a specific view is used?
Are you building devtools or other things which need information about the codebase? Do you need structured schema information of your Eloquent data model?

These are all use cases where you need to introspect your codebase and find out where things are used, how they are used and what they are. This package does exactly that.

<br />

## Installation

Install the package via composer:

```bash  
composer require mateffy/laravel-introspect  
```  

> [!NOTE]  
> Depending on your use case, it might make sense to install the package as a dev dependency by adding the `--dev` flag to the command.

<br />

## Usage

```php  
use Mateffy\Introspect\Facades\Introspect;  

$views = Introspect::views()
    ->whereNameEquals('components.*.button')
    ->whereUsedBy('pages.admin.*')
    ->get();  
    
$routes = Introspect::routes()
    ->whereUsesController(MyController::class)
    ->whereUsesMiddleware('auth')
    ->whereUsesMethod('POST')
    ->get();  

$classes = Introspect::classes()
    ->whereImplements(MyInterface::class)
    ->whereUses(MyTrait::class)
    ->get();  
    
$models = Introspect::models()
    ->whereHasProperties(['name', 'email'])
    ->whereHasFillable('password')
    ->get();  

// Access Eloquent properties, relationships, casts, etc. directly
$detail = Introspect::model(User::class);

// Model to JSON schema
$schema = $detail->schema();
```  

- [Views](#views)
- [Routes](#routes)
- [Classes](#generic-classes)
- [Models](#models)
- [Commands](#commands)
- [Chaining queries with OR and AND](#chaining-orand)

<br />

### Views

You can query all of the views you have in your codebase, including those that are provided by other packages and are
namespaced with a `prefix::`.
View queries return a `Collection<string>` of view names.

> All queries support wildcards, e.g. `components.*.button` or `*.button`

#### Query views by view path

```php  
$views = Introspect::views()  
    // Supports wildcards 
    ->whereNameEquals('*components.*item')
    ->get();
// -> ['components.item', 'filament::components.dropdown.list.item', ...]
    
$views = Introspect::views()  
    ->whereNameStartsWith('filament::')
    ->get();  

$views = Introspect::views()  
    ->whereNameEndsWith('button')
    ->get();  

$views = Introspect::views()  
    ->whereNameContains('button')
    ->get();
```  

#### Query all views that are used by specific views

```php  
$routes = Introspect::views()  
    ->whereUsedBy('pages.welcome')
    ->get();
// -> ['components.button', 'filament::components.button', ...]
    
$routes = Introspect::views()  
    ->whereUsedBy('pages.*')
    ->get();
    
$routes = Introspect::views()  
    ->whereNotUsedBy('pages.*')
    ->get();
```  

#### Query all views that use a specific view

```php  
$routes = Introspect::views()  
    ->whereUses('components.button')   
    ->get();
    
$routes = Introspect::views()  
    ->whereUses('*.button')   
    ->get();
    
$routes = Introspect::views()  
    ->whereDoesntUse('*.button')   
    ->get();
```  

#### Query all views that extend a specific view

```php  
$routes = Introspect::views()  
    ->whereExtends('layouts.app')   
    ->get();
    
$routes = Introspect::views()  
    ->whereExtends('layouts.*')   
    ->get();
    
$routes = Introspect::views()  
    ->whereDoesntExtend('layouts.*')   
    ->get();
```

<br />

### Routes

Query through all the routes registered in your application (think like `artisan route:list` output), including those
registered by packages.
The routes are returned as a `Collection<\Illuminate\Routing\Route>`.

#### Query all routes that use a controller

```php  
$routes = Introspect::routes()  
    ->whereUsesController(MyController::class)  
    ->get();
// -> [\Illuminate\Routing\Route, \Illuminate\Routing\Route, ...]

$routes = Introspect::routes()  
    ->whereUsesController(MyController::class, 'index')
    ->get();
    
$routes = Introspect::routes()  
    ->whereUsesController(SingleActionController::class, 'index')
    ->get();
```  

#### Query all routes that use a specific middleware

```php  
$routes = Introspect::routes()  
    ->whereUsesMiddleware(MyMiddleware::class)  
    ->get();

$routes = Introspect::routes()  
    ->whereUsesMiddlewares(['tenant', 'auth'])  
    ->get();
    
$routes = Introspect::routes()  
    // Match any of the middlewares
    ->whereUsesMiddlewares(['tenant', 'auth'], all: false)
    ->get();
    
$routes = Introspect::routes()  
    ->whereDoesntUseMiddleware('api')  
    ->get();
```  

#### Query routes by name

> "Name equals/contains" queries support wildcards, e.g. `api.products.*` or `*.products.*`

```php  
$routes = Introspect::routes()  
    ->whereNameEquals('api.products.*')
    ->get();
    
$routes = Introspect::routes()  
    ->whereNameStartsWith('api.products.')
    ->get();

$routes = Introspect::routes()  
    ->whereNameEndsWith('api.products.')
    ->get();
    
$routes = Introspect::routes()  
    ->whereNameDoesntEqual('api.products.*')
    ->get();
```  

#### Query routes by path

> "Path equals/contains" queries support wildcards, e.g. `api/products/*` or `*/products/*`

```php  
$routes = Introspect::routes()  
    ->wherePathStartsWith('api/products')
    ->get();

$routes = Introspect::routes()
    ->wherePathEndsWith('products')
    ->get();

$routes = Introspect::routes()
    ->wherePathContains('products')
    ->get();

$routes = Introspect::routes()
    ->wherePathEquals('api/products*')
    ->get();
```

<br />

### Generic Classes

#### Query by namespace

```php
$services = Introspect::classes()  
    ->whereName('\App\Services')
    ->get();
```

#### Query by parent

```php
$blocks = Introspect::classes()  
    ->whereExtends(CMS\Block::class)
    ->get();
```

#### Query by interface

```php
$blocks = Introspect::classes()  
    ->whereImplements(CMS\Block::class)
    ->get();
```

#### Query by trait

```php
$blocks = Introspect::classes()  
    ->whereUses(MyTrait::class)
    ->get();
```

<br />

### Models

Query Eloquent models based on their properties, attributes (fillable, hidden, appended, readable, writable), and relationship existence. Model queries return a `Collection` of model class strings (e.g., `App\Models\User::class`).

#### Query by Property Existence

Filter models based on whether they possess specific properties.

```php
$models = Introspect::models()
    ->whereHasProperty('created_at')
    ->get();

// Also available: whereDoesntHaveProperty, whereHasProperties, whereDoesntHaveProperties
// Example with multiple properties (any):
$models = Introspect::models()
    ->whereHasProperties(['first_name', 'last_name'], all: false)
    ->get();
```

#### Query by Fillable Properties

Filter models based on their `$fillable` attributes.

```php
$models = Introspect::models()
    ->whereHasFillable('title')
    ->get();

// Also available: whereDoesntHaveFillable, whereHasFillableProperties, etc.
$models = Introspect::models()
    ->whereHasFillableProperties(['name', 'description']) // all: true by default
    ->get();
```

#### Query by Hidden Properties

Filter models based on their `$hidden` attributes.

```php
$models = Introspect::models()
    ->whereHasHidden('password')
    ->get();

// Also available: whereDoesntHaveHidden, whereHasHiddenProperties, etc.
$models = Introspect::models()
    ->whereDoesntHaveHiddenProperties(['name', 'email'], all: false) // neither name nor email are hidden
    ->get();
```

#### Query by Appended Properties

Filter models based on their `$appends` attributes (accessors).

```php
$models = Introspect::models()
    ->whereHasAppended('full_name')
    ->get();

// Also available: whereDoesntHaveAppended, whereHasAppendedProperties, etc.
$models = Introspect::models()
    ->whereHasAppendedProperties(['is_active', 'resource_type'])
    ->get();
```

#### Query by Readable Properties

Filter models based on "readable" properties (public getters, public properties, or attributes).

```php
$models = Introspect::models()
    ->whereHasReadable('name')
    ->get();

// Also available: whereDoesntHaveReadable, whereHasReadableProperties, etc.
$models = Introspect::models()
    ->whereHasReadableProperties(['id', 'email'])
    ->get();
```

#### Query by Writable Properties

Filter models based on "writable" properties (public setters or public properties).

```php
$models = Introspect::models()
    ->whereHasWritable('status')
    ->get();

// Also available: whereDoesntHaveWritable, whereHasWritableProperties, etc.
$models = Introspect::models()
    ->whereHasWritableProperties(['name', 'settings'])
    ->get();
```

#### Query by Relationship Existence

Filter models based on the existence of specific relationship methods.
*Note: This currently checks for method presence, not relationship type or related model details.*

```php
$models = Introspect::models()
    ->whereHasRelationship('user')
    ->get();

$models = Introspect::models()
    ->whereDoesntHaveRelationship('logs')
    ->get();
```

<br />

### Chaining queries with `OR` and `AND`

By default, any queries are combined with `AND` logic.
However, you can craft more complex queries by chaining together queries with `OR` logic, too.
This works for all queries, including models, routes, views and classes.

```php
use \Mateffy\Introspect\Query\Contracts\RouteQueryInterface;

$routes = Introspect::routes()  
    ->whereNameEquals('api.*')
    ->whereMethod('POST')
    ->or(fn (RouteQueryInterface $query) => $query
        ->whereHasParameter('product') //
        ->whereHasParameter('category')
    )
    ->get();
```

Some methods support multiple parameters, e.g. `whereUsesMiddlewares([...])` or `whereUsesProperties([...])`.
These methods have an `all` parameter that defaults to `true`. If set to `false`, the values are checked with `OR` logic
too, matching on any of the values.

```php
$routes = Introspect::routes()  
    ->whereUsesMiddlewares(['tenant', 'auth'], all: false)  
    ->get();
```

<br />

### Limit the results and paginate just like using Eloquent queries

_Actual Laravel pagination (`->paginate(...)`) is not yet supported, but you can use `limit` and `offset` to get the
results you want._

```php  
$models = Introspect::models()  
    ->limit(10)
    ->offset(20)
    ->get();
```  

<br />

## DTO Examples

#### Get all model properties

```php
$model = Introspect::model(User::class);
$properties = $models->properties();
```

#### Get Model as JSON schema

```php
$schema = Introspect::model(User::class)->schema();
// -> ['type' => 'object',...]
```

<br />

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
