# Introspect for Laravel

A utility library to analyze and pull information from Laravel codebases, querying model, route and more Laravel-specific things directly from your codebase using PHP reflection.  
It is targeted for development tools and coding agents for better understanding of the codebase.

## Who is this for?  
  
Are you building a devtool or other application that needs to introspect a Laravel codebase?  
Then this package will make your life a lot easier by providing a fluent API to query models, routes, controllers, views and more.

<br />

- Query views, routes, classes and models with a fluent API
- Parse properties, relationships + their types and more from Eloquent models
- (De-)serialize queries to/from JSON (for LLM Tools)

<br />

| Query      | Available Filters                                                      |
|------------|------------------------------------------------------------------------|
| Views      | name, path, used by view, uses view, extends                           |
| Routes     | name, URI, controller + fn, methods, middleware                        |
| Classes    | name / namespace, extends parent, implements interfaces, uses traits   |
| ↳ Models   | ... relationships, properties, casts, fillable, hidden, read/writeable |
| ⤷ Commands | ... signature, description                                             |
  
> Name and a few other queries support even support wildcards (e.g. `components.*.paragraph`)

<br />

## Installation  
  
Install the package via composer:  
  
```bash  
composer require mateffy/laravel-introspect  
```  

> [!NOTE]  
> The package is still in development and not tagged, you will be installing the `dev-main` branch for now.
  
<br />

## Usage  
  
```php  
use Mateffy\Introspect\Facades\Introspect;  

$views = Introspect::views()->get();  
$routes = Introspect::routes()->get();  
$classes = Introspect::classes()->get();  
$models = Introspect::models()->get();
$commands = Introspect::commands()->get();  

// Access Eloquent properties, relationships, casts, etc. directly
$detail = Introspect::model(User::class);

// Model to JSON schema
$schema = $detail->schema();
```  

<br />

## Query Examples  
### Models
#### Query by relationship
```php  
$models = Introspect::models()  
    ->whereRelationship('users')  
    ->get();

$models = Introspect::models()  
    ->whereRelationship('users', HasMany::class)  
    ->get();

$models = Introspect::models()  
    ->whereRelatesTo(User::class)  
    ->get();

$models = Introspect::models()  
    ->whereRelatedTo(User::class)  
    ->get();

$models = Introspect::models()  
    ->whereHasProperty('name')  
    ->get();

$models = Introspect::models()  
    ->whereDoesntHaveProperty('name')  
    ->get();

$models = Introspect::models()  
    ->whereSetter('name')  
    ->get();
    
$models = Introspect::models()  
    ->whereFillable('name')  
    ->get();

$models = Introspect::models()  
    ->whereNotFillable('name')  
    ->get();

$models = Introspect::models()  
    ->whereGuarded('name')  
    ->get();

$models = Introspect::models()  
    ->whereNotGuarded('name')  
    ->get();

$models = Introspect::models()  
    ->whereGetter('name')  
    ->get();

$models = Introspect::models()  
    ->whereSetter('name')  
    ->get();
    
$models = Introspect::models()  
    ->whereSetter('name')  
    ->get();

$models = Introspect::models()  
    ->whereHidden('name')  
    ->get();

$models = Introspect::models()  
    ->whereNotHidden('name')  
    ->get();
    
$models = Introspect::models()  
    ->whereCast('name')
    ->get();

$models = Introspect::models()  
    ->whereDoesntCast('name')
    ->get();
    
$models = Introspect::models()  
    ->whereCast('is_admin', 'boolean')
    ->get();
    
$models = Introspect::models()  
    ->whereCastWith(CustomCoordinateCast::class)
    ->get();
```  

### Routes
#### Query all routes that use a controller  
```php  
$routes = Introspect::routes()  
    ->whereController(MyController::class)  
    ->whereMethod('index')    
    ->get();
```  

#### Query all routes that use a specific middleware  
```php  
$routes = Introspect::routes()  
    ->whereMiddleware(MyMiddleware::class)  
    ->whereMiddleware('web')
    ->get();
```  

#### Query routes by name
```php  
$routes = Introspect::routes()  
    ->whereNameStartsWith('api.products.')
    ->get();

$routes = Introspect::routes()  
    ->whereNameEndsWith('api.products.')
    ->get();
```  

#### Query routes by name
```php  
$routes = Introspect::routes()  
    ->whereNameStartswith('api.products.')
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

$routes = Introspect::views()  
    ->whereExtends('layouts.app')   
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

### Generic Classes

#### Query by namespace
```php
$services = Introspect::classes()  
    ->whereNamespace('\App\Services')
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
  

## DTO Examples

#### Get all model properties
```php
$model = Introspect::model(User::class);
$properties = $models->properties();
$casts = $models->casts();
$casts = $models->casts();
```

#### Get Model as JSON schema
```php
$schema = Introspect::model(User::class)->schema();
// -> ['type' => 'object',...]
```

  
## License  
  
The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
