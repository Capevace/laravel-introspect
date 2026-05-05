# Laravel Introspect Test Coverage Plan

## Overview
This document contains the detailed implementation plan for achieving comprehensive test coverage of the Laravel Introspect package.

## Guidelines (From User)
1. **Test Style**: Use Pest PHP functional style (same as existing tests)
2. **Mock Strategy**: Mock as little as possible - prefer real classes
3. **Organization**: Work by category (complete one before moving to next)
4. **Commands**: Skip interactive command tests, only test non-interactive ones

---

## Category 1: DTO Tests (`tests/Unit/Dto/`)

### ModelTest.php
Tests for `Mateffy\Introspect\DTO\Model`:

- `it('can create a model DTO')` - Basic instantiation with classpath, description, properties
- `it('can generate schema with all properties')` - Test schema() method with full configuration
- `it('filters out properties with non-standard types from schema')` - Test filtering of complex types
- `it('marks nullable properties correctly in required list')` - Test null type handling in required array
- `it('generates schema with single type when not supporting multiple types')` - Test supportsMultipleTypes=false
- `it('returns null for description when it is empty string')` - Test empty description handling
- `it('can create model DTO from actual model reflector')` - Integration test using introspect()->model()

### ModelPropertyTest.php
Tests for `Mateffy\Introspect\DTO\ModelProperty`:

- `it('can create a model property DTO')` - Basic instantiation
- `it('can create a hidden property')` - Test hidden=true scenario
- `it('can create a read-only property')` - Test readable=true, writable=false
- `it('can create a write-only property')` - Test readable=false, writable=true
- `it('can create a relation property')` - Test relation=true, appended=true
- `it('can handle null values for optional fields')` - Test nullable description/default/cast
- `it('can handle multiple types')` - Test types collection with multiple entries

### RouteTest.php
Tests for `Mateffy\Introspect\DTO\Route`:

- `it('can create a route DTO from a Laravel route')` - Test fromRoute() with closure
- `it('can create a route DTO from a controller route')` - Test fromRoute() with controller class
- `it('can create a route DTO from an invokable controller')` - Test __invoke detection
- `it('handles routes with parameters')` - Test routes with {id} parameters
- `it('handles routes with middleware')` - Test middleware array extraction
- `it('converts route to array correctly')` - Test toArray() method
- `it('handles routes without names')` - Test null name handling
- `it('handles various HTTP methods')` - Test GET, POST, PUT, DELETE, PATCH

### ViewPathTest.php
Tests for `Mateffy\Introspect\DTO\ViewPath`:

- `it('can create a view path DTO')` - Basic instantiation
- `it('can create a view path DTO with nested view')` - Test dot-notation paths
- `it('can create a view path DTO with vendor namespace')` - Test vendor::package.view format
- `it('handles root level views')` - Test simple view names

### ViewRepositoryTest.php
Tests for `Mateffy\Introspect\DTO\ViewRepository`:

- `it('can create a view repository DTO')` - Basic instantiation with single view
- `it('can create a view repository with multiple views')` - Test collections
- `it('can create an empty view repository')` - Test empty collection handling
- `it('can create a vendor view repository')` - Test vendor namespaces
- `it('preserves nested view paths in repository')` - Test nested path preservation

---

## Category 2: Support Class Tests (`tests/Unit/Support/`)

### RegexHelperTest.php
Tests for `Mateffy\Introspect\Support\RegexHelper`:

- `it('escapes special regex characters')` - Test escape() with . + ? ( ) [ ] { } ^ $ | /
- `it('converts wildcards to regex patterns')` - Test * becomes .*
- `it('matches exact string without wildcards')` - Test matches() with exact match
- `it('matches wildcard patterns')` - Test matches() with * wildcards
- `it('matches at start with wildcard prefix')` - Test *test pattern
- `it('matches at end with wildcard suffix')` - Test test* pattern
- `it('matches in middle with wildcards')` - Test *test* pattern
- `it('handles multiple wildcards')` - Test *foo*bar* pattern
- `it('returns false for non-matching patterns')` - Test negative cases
- `it('handles empty patterns')` - Test edge case with empty string
- `it('handles patterns with backslashes')` - Test backslash escaping
- `it('matches case-insensitively')` - Test case insensitivity

### ControllableConsoleOutputTest.php
Tests for `Mateffy\Introspect\Support\ControllableConsoleOutput`:

- `it('outputs results as json when format is json')` - Test JSON output
- `it('outputs results as table when format is not specified')` - Test default table output
- `it('outputs only count when count option is true')` - Test count mode
- `it('warns when no results found')` - Test empty results handling
- `it('uses custom row formatter')` - Test closure-based row formatting

### PaginatableConsoleOutputTest.php
Tests for `Mateffy\Introspect\Support\PaginatableConsoleOutput`:

- `it('applies offset to query')` - Test offset option handling
- `it('applies limit to query')` - Test limit option handling
- `it('applies both offset and limit')` - Test combined pagination
- `it('returns unchanged query when no pagination options')` - Test no-op case
- `it('converts string options to integers')` - Test type casting

---

## Category 3: Reflection Tests (`tests/Unit/Reflection/`)

### ClassReflectorTest.php
Tests for `Mateffy\Introspect\Reflection\ClassReflector`:

- `it('checks if class extends another class')` - Test extends() method
- `it('gets nested parent classes')` - Test getNestedParents() inheritance chain
- `it('handles classes with no parents')` - Test base class case
- `it('gets nested interfaces')` - Test getNestedInterfaces()
- `it('handles classes with no interfaces')` - Test empty interfaces
- `it('gets nested traits')` - Test getNestedTraits()
- `it('handles classes with no traits')` - Test empty traits
- `it('handles errors gracefully when getting parents')` - Test exception handling with Log
- `it('handles errors gracefully when getting interfaces')` - Test exception handling
- `it('handles errors gracefully when getting traits')` - Test exception handling
- `it('removes leading backslashes from class names')` - Test normalization

### ModelReflectorTest.php
Tests for `Mateffy\Introspect\Reflection\ModelReflector`:

- `it('creates reflector from classpath')` - Test makeFromClasspath()
- `it('throws exception for non-existent class')` - Test error case
- `it('throws exception for non-model class')` - Test validation
- `it('creates reflector from reflection')` - Test makeFromReflection()
- `it('throws exception for abstract class')` - Test abstract class error
- `it('gets model name')` - Test getName()
- `it('converts to array')` - Test toArray()
- `it('creates DTO')` - Test dto() method
- `it('extracts description from docblock')` - Test docblock parsing
- `it('handles empty docblock')` - Test null description
- `it('cleans up multiline docblock descriptions')` - Test description cleanup

### HasPropertiesTest.php
Tests for `Mateffy\Introspect\Reflection\ModelReflector\HasProperties`:

- `it('extracts fillable properties')` - Test $fillable handling
- `it('extracts hidden properties')` - Test $hidden handling
- `it('extracts casts')` - Test $casts handling
- `it('extracts appended attributes')` - Test $appends handling
- `it('determines if property is readable')` - Test getter detection
- `it('determines if property is writable')` - Test setter detection
- `it('detects relation properties')` - Test relation method detection
- `it('parses docblock for property types')` - Test @var annotation parsing
- `it('parses docblock for property descriptions')` - Test description extraction
- `it('handles default values')` - Test default value extraction
- `it('combines all property sources')` - Test merge of fillable/hidden/casts/appends
- `it('handles model with no properties defined')` - Test empty model case

---

## Category 4: Where Clause Tests (`tests/Unit/Query/Where/`)

### Class Where Tests (`tests/Unit/Query/Where/Classes/`)

#### WhereClassNameContainsTest.php
- `it('filters classes by name containing text')` - Test basic filtering
- `it('supports wildcard patterns')` - Test * wildcards
- `it('inverts with not flag')` - Test not=true
- `it('handles case sensitivity')` - Test case handling
- `it('returns false for non-matching classes')` - Test negative case

#### WhereClassNameEndsWithTest.php
- `it('filters classes by name ending with text')` - Test suffix matching
- `it('supports wildcard patterns')` - Test * wildcards at start
- `it('inverts with not flag')` - Test not=true

#### WhereClassNameEqualsTest.php
- `it('filters classes by exact name match')` - Test equality
- `it('supports wildcard patterns')` - Test full wildcard patterns
- `it('inverts with not flag')` - Test not=true

#### WhereClassNameStartsWithTest.php
- `it('filters classes by name starting with text')` - Test prefix matching
- `it('supports wildcard patterns')` - Test * wildcards at end
- `it('inverts with not flag')` - Test not=true

#### WhereExtendsClassTest.php
- `it('filters classes extending a specific parent')` - Test single parent
- `it('filters classes extending all specified parents')` - Test all=true with multiple
- `it('filters classes extending any specified parent')` - Test all=false with multiple
- `it('supports wildcard patterns in parent names')` - Test * wildcards
- `it('inverts with not flag')` - Test not=true
- `it('handles deeply nested inheritance')` - Test grandparent detection

#### WhereImplementsInterfacesTest.php
- `it('filters classes implementing a specific interface')` - Test single interface
- `it('filters classes implementing all specified interfaces')` - Test all=true
- `it('filters classes implementing any specified interface')` - Test all=false
- `it('supports wildcard patterns')` - Test * wildcards
- `it('inverts with not flag')` - Test not=true

#### WhereUsesTraitsTest.php
- `it('filters classes using a specific trait')` - Test single trait
- `it('filters classes using all specified traits')` - Test all=true
- `it('filters classes using any specified traits')` - Test all=false
- `it('supports wildcard patterns')` - Test * wildcards
- `it('inverts with not flag')` - Test not=true

### Model Where Tests (`tests/Unit/Query/Where/Models/`)

#### WhereHasAppendedPropertiesTest.php
- `it('filters models with appended properties')` - Test basic filtering
- `it('filters models without appended properties')` - Test not=true

#### WhereHasFillablePropertiesTest.php
- `it('filters models with fillable properties')` - Test basic filtering
- `it('filters models without fillable properties')` - Test not=true

#### WhereHasHiddenPropertiesTest.php
- `it('filters models with hidden properties')` - Test basic filtering
- `it('filters models without hidden properties')` - Test not=true

#### WhereHasPropertiesTest.php
- `it('filters models having all specified properties')` - Test all=true
- `it('filters models having any specified properties')` - Test all=false
- `it('inverts with not flag')` - Test not=true

#### WhereHasReadablePropertiesTest.php
- `it('filters models with readable properties')` - Test basic filtering
- `it('filters models without readable properties')` - Test not=true

#### WhereHasRelationPropertiesTest.php
- `it('filters models with relation properties')` - Test basic filtering
- `it('filters models without relation properties')` - Test not=true

#### WhereHasWritablePropertiesTest.php
- `it('filters models with writable properties')` - Test basic filtering
- `it('filters models without writable properties')` - Test not=true

### Route Where Tests (`tests/Unit/Query/Where/Routes/`)

#### WhereRouteHasParametersTest.php
- `it('filters routes having all specified parameters')` - Test all=true
- `it('filters routes having any specified parameters')` - Test all=false
- `it('inverts with not flag')` - Test not=true

#### WhereRouteNameContainsTest.php
- `it('filters routes by name containing text')` - Test basic filtering
- `it('supports wildcard patterns')` - Test * wildcards
- `it('inverts with not flag')` - Test not=true

#### WhereRouteNameEndsWithTest.php
- `it('filters routes by name ending with text')` - Test suffix matching
- `it('supports wildcard patterns')` - Test * wildcards
- `it('inverts with not flag')` - Test not=true

#### WhereRouteNameEqualsTest.php
- `it('filters routes by exact name match')` - Test equality
- `it('supports wildcard patterns')` - Test full patterns
- `it('inverts with not flag')` - Test not=true

#### WhereRouteNameStartsWithTest.php
- `it('filters routes by name starting with text')` - Test prefix matching
- `it('supports wildcard patterns')` - Test * wildcards
- `it('inverts with not flag')` - Test not=true

#### WhereRoutePathContainsTest.php
- `it('filters routes by path containing text')` - Test basic filtering
- `it('supports wildcard patterns')` - Test * wildcards
- `it('inverts with not flag')` - Test not=true

#### WhereRoutePathEndsWithTest.php
- `it('filters routes by path ending with text')` - Test suffix matching
- `it('supports wildcard patterns')` - Test * wildcards
- `it('inverts with not flag')` - Test not=true

#### WhereRoutePathEqualsTest.php
- `it('filters routes by exact path match')` - Test equality
- `it('supports wildcard patterns')` - Test full patterns
- `it('inverts with not flag')` - Test not=true

#### WhereRoutePathStartsWithTest.php
- `it('filters routes by path starting with text')` - Test prefix matching
- `it('supports wildcard patterns')` - Test * wildcards
- `it('inverts with not flag')` - Test not=true

#### WhereRouteUsesControllerTest.php
- `it('filters routes by controller class')` - Test single controller
- `it('filters routes by multiple controllers')` - Test array of controllers
- `it('supports wildcard patterns')` - Test * wildcards in controller name
- `it('inverts with not flag')` - Test not=true

#### WhereRouteUsesMethodsTest.php
- `it('filters routes by HTTP methods')` - Test GET, POST, etc.
- `it('filters routes by all specified methods')` - Test all=true
- `it('filters routes by any specified methods')` - Test all=false
- `it('inverts with not flag')` - Test not=true

#### WhereRouteUsesMiddlewaresTest.php
- `it('filters routes by middleware')` - Test single middleware
- `it('filters routes by all specified middlewares')` - Test all=true
- `it('filters routes by any specified middlewares')` - Test all=false
- `it('inverts with not flag')` - Test not=true

### View Where Tests (`tests/Unit/Query/Where/Views/`)

#### WhereUsesViewTest.php
- `it('filters views that include a specific view')` - Test @include detection
- `it('filters views that use a component')` - Test <x- component detection
- `it('filters views that use @component')` - Test @component detection
- `it('supports wildcard patterns')` - Test * wildcards
- `it('uses lax mode when enabled')` - Test lax=true
- `it('inverts with not flag')` - Test not=true

#### WhereUsedByViewTest.php
- `it('filters views used by a specific view')` - Test reverse lookup
- `it('supports wildcard patterns')` - Test * wildcards
- `it('inverts with not flag')` - Test not=true

#### WhereViewNameContainsTest.php
- `it('filters views by name containing text')` - Test basic filtering
- `it('supports wildcard patterns')` - Test * wildcards
- `it('inverts with not flag')` - Test not=true

#### WhereViewNameEndsWithTest.php
- `it('filters views by name ending with text')` - Test suffix matching
- `it('supports wildcard patterns')` - Test * wildcards
- `it('inverts with not flag')` - Test not=true

#### WhereViewNameEqualsTest.php
- `it('filters views by exact name match')` - Test equality
- `it('supports wildcard patterns')` - Test full patterns
- `it('inverts with not flag')` - Test not=true

#### WhereViewNameStartsWithTest.php
- `it('filters views by name starting with text')` - Test prefix matching
- `it('supports wildcard patterns')` - Test * wildcards
- `it('inverts with not flag')` - Test not=true

---

## Category 5: Query Component Tests (`tests/Unit/Query/`)

### WhereSerializerTest.php
Tests for `Mateffy\Introspect\Query\Where\WhereSerializer`:

- `it('serializes where clauses to array')` - Test serialize()
- `it('deserializes nested where clauses')` - Test deserialize() for NestedWhere
- `it('deserializes view where clauses')` - Test all view where types
- `it('deserializes route where clauses')` - Test all route where types
- `it('deserializes class where clauses')` - Test all class where types
- `it('throws exception for unknown where type')` - Test error handling
- `it('maintains data integrity through serialization round-trip')` - Test encode/decode

### NestedWhereTest.php
Tests for `Mateffy\Introspect\Query\Where\NestedWhere`:

- `it('filters with AND logic by default')` - Test all where clauses must pass
- `it('filters with OR logic when specified')` - Test any where clause can pass
- `it('inverts result with not flag')` - Test not=true
- `it('handles empty where collection')` - Test empty case
- `it('creates subquery')` - Test createSubquery()
- `it('serializes to array')` - Test toArray()
- `it('deserializes from array')` - Test fromArray()
- `it('handles nested nested where clauses')` - Test deep nesting

---

## Category 6: View Analysis Tests (`tests/Unit/View/`)

### ViewAnalysisTest.php
Tests for `Mateffy\Introspect\View\ViewAnalysis`:

- `it('detects @include directives')` - Test @include('view.name')
- `it('detects @include with multiline')` - Test multi-line @include
- `it('detects @component directives')` - Test @component('view.name')
- `it('detects <x- component syntax')` - Test <x-view.name>
- `it('detects self-closing component tags')` - Test <x-view.name />
- `it('handles component names without components. prefix')` - Test prefix stripping
- `it('supports wildcard patterns in view names')` - Test * wildcards
- `it('handles namespace prefixes')` - Test vendor::package.view format
- `it('uses lax mode for simple string matching')` - Test lax=true
- `it('returns false when view is not included')` - Test negative case
- `it('handles views with similar names')` - Test partial match edge cases
- `it('handles deeply nested view paths')` - Test multiple directory levels
- `it('handles edge case with multiple wildcards')` - Test *foo*bar* patterns
- `it('throws exception for non-existent view')` - Test error handling

---

## Category 7: Core Class Tests (`tests/Unit/`)

### LaravelIntrospectTest.php
Tests for `Mateffy\Introspect\LaravelIntrospect`:

- `it('creates instance with default directories')` - Test default path
- `it('creates instance with custom directories')` - Test custom path
- `it('returns model DTO')` - Test model() method
- `it('returns class query')` - Test classes() method
- `it('returns model query')` - Test models() method
- `it('returns view query')` - Test views() method
- `it('returns route query')` - Test routes() method
- `it('queries use configured path')` - Test path propagation

### LaravelIntrospectServiceProviderTest.php
Tests for `Mateffy\Introspect\LaravelIntrospectServiceProvider`:

- `it('registers package configuration')` - Test config publishing
- `it('registers commands')` - Test command registration
- `it('binds ModelQueryInterface')` - Test interface binding
- `it('binds ClassQueryInterface')` - Test interface binding
- `it('binds ViewQueryInterface')` - Test interface binding
- `it('binds RouteQueryInterface')` - Test interface binding
- `it('provides correct package name')` - Test package name

### IntrospectFacadeTest.php
Tests for `Mateffy\Introspect\Facades\Introspect`:

- `it('provides access to LaravelIntrospect')` - Test facade resolution
- `it('delegates model() call')` - Test model() via facade
- `it('delegates classes() call')` - Test classes() via facade
- `it('delegates models() call')` - Test models() via facade
- `it('delegates views() call')` - Test views() via facade
- `it('delegates routes() call')` - Test routes() via facade

---

## Category 8: Index Tests (`tests/Unit/Index/`)

### IndexBuilderTest.php
Tests for `Mateffy\Introspect\Query\Index\IndexBuilder`:

- `it('creates database if not exists')` - Test ensureDbExists()
- `it('returns false when database already exists')` - Test existing db case
- `it('creates SQLite connection')` - Test db() method
- `it('uses custom db path when provided')` - Test constructor path injection
- `it('uses default db path when not provided')` - Test default path
- `it('disconnects and purges existing connections')` - Test connection cleanup

### IndexMigrationTest.php
Tests for `Mateffy\Introspect\Query\Index\IndexMigration`:

- `it('creates migrations table on up')` - Test up() method
- `it('drops migrations table on down')` - Test down() method
- `it('handles existing migrations table')` - Test idempotency

---

## Category 9: Command Tests (`tests/Feature/Commands/`)

### Summary of Commands to Test

**8 Total Commands Found:**
1. ✅ **IntrospectCommand** - Base class (non-interactive, TEST)
2. ❌ **IntrospectInteractiveCommand** - Interactive prompts (SKIP)
3. ✅ **IntrospectViewsCommand** - Views query (TEST)
4. ✅ **IntrospectSchemaCommand** - Schema discovery (TEST) ⭐ NEW
5. ✅ **IntrospectModelCommand** - Single model detail (TEST) ⭐ NEW
6. ✅ **IntrospectModelsCommand** - Models query (TEST) ⭐ NEW
7. ✅ **IntrospectClassesCommand** - Classes query (TEST) ⭐ NEW
8. ✅ **IntrospectRoutesCommand** - Routes query (TEST) ⭐ NEW

**5 New Commands Added by User:**
- IntrospectSchemaCommand
- IntrospectModelCommand  
- IntrospectModelsCommand
- IntrospectClassesCommand
- IntrospectRoutesCommand

### IntrospectSchemaCommandTest.php
Tests for `Mateffy\Introspect\Commands\IntrospectSchemaCommand`:

- `it('outputs all resources schema by default')` - Test no --resource option
- `it('outputs views schema specifically')` - Test --resource=views
- `it('outputs routes schema specifically')` - Test --resource=routes
- `it('outputs classes schema specifically')` - Test --resource=classes
- `it('outputs models schema specifically')` - Test --resource=models
- `it('throws error for unknown resource')` - Test invalid resource
- `it('outputs json format')` - Test --format=json
- `it('outputs table format')` - Test --format=table
- `it('outputs compact json')` - Test --compact
- `it('includes dsl operators in output')` - Test DSL operators section
- `it('includes example queries for each resource')` - Test example queries
- `it('returns error on failure')` - Test error handling

### IntrospectModelCommandTest.php
Tests for `Mateffy\Introspect\Commands\IntrospectModelCommand`:

- `it('outputs model details by class argument')` - Test basic execution
- `it('outputs json format')` - Test --format=json
- `it('outputs table format')` - Test --format=table
- `it('outputs compact json')` - Test --compact
- `it('outputs json schema when schema flag is set')` - Test --schema option
- `it('outputs table with properties section')` - Test table output sections
- `it('outputs table with fillable section')` - Test fillable display
- `it('outputs table with hidden section')` - Test hidden display
- `it('outputs table with casts section')` - Test casts display
- `it('outputs table with relationships section')` - Test relationships display
- `it('throws error for non-existent model class')` - Test error handling
- `it('throws error for non-model class')` - Test validation error

### IntrospectModelsCommandTest.php
Tests for `Mateffy\Introspect\Commands\IntrospectModelsCommand`:

- `it('executes with no options')` - Test basic execution
- `it('filters by name option')` - Test --name filter
- `it('filters by name-starts option')` - Test --name-starts filter
- `it('filters by name-ends option')` - Test --name-ends filter
- `it('filters by name-contains option')` - Test --name-contains filter
- `it('filters by extends option')` - Test --extends filter
- `it('filters by implements option with single interface')` - Test --implements
- `it('filters by implements option with multiple interfaces')` - Test comma-separated
- `it('filters by uses option with single trait')` - Test --uses
- `it('filters by uses option with multiple traits')` - Test comma-separated traits
- `it('filters by has-property option')` - Test --has-property
- `it('filters by has-properties option')` - Test --has-properties
- `it('filters by has-fillable option')` - Test --has-fillable
- `it('filters by has-fillable-all option')` - Test --has-fillable-all
- `it('filters by has-fillable-any option')` - Test --has-fillable-any
- `it('filters by has-hidden option')` - Test --has-hidden
- `it('filters by has-hidden-all option')` - Test --has-hidden-all
- `it('filters by has-hidden-any option')` - Test --has-hidden-any
- `it('filters by has-appended option')` - Test --has-appended
- `it('filters by has-appended-all option')` - Test --has-appended-all
- `it('filters by has-appended-any option')` - Test --has-appended-any
- `it('filters by has-readable option')` - Test --has-readable
- `it('filters by has-readable-all option')` - Test --has-readable-all
- `it('filters by has-readable-any option')` - Test --has-readable-any
- `it('filters by has-writable option')` - Test --has-writable
- `it('filters by has-writable-all option')` - Test --has-writable-all
- `it('filters by has-writable-any option')` - Test --has-writable-any
- `it('filters by has-relationship option')` - Test --has-relationship
- `it('applies dsl query option')` - Test --query
- `it('applies dsl query from file option')` - Test --query-file
- `it('outputs json format')` - Test --format=json
- `it('outputs table format')` - Test --format=table
- `it('outputs csv format')` - Test --format=csv
- `it('outputs raw format')` - Test --format=raw
- `it('outputs jsonl format')` - Test --format=jsonl
- `it('outputs compact json')` - Test --compact
- `it('applies offset and limit')` - Test pagination
- `it('outputs count only')` - Test --count
- `it('selects specific fields')` - Test --fields
- `it('transforms model DTO to array correctly')` - Test transformModel()
- `it('returns error on failure')` - Test error handling

### IntrospectClassesCommandTest.php
Tests for `Mateffy\Introspect\Commands\IntrospectClassesCommand`:

- `it('executes with no options')` - Test basic execution
- `it('filters by name option')` - Test --name filter
- `it('filters by name-not option')` - Test --name-not filter
- `it('filters by name-starts option')` - Test --name-starts filter
- `it('filters by name-ends option')` - Test --name-ends filter
- `it('filters by name-contains option')` - Test --name-contains filter
- `it('filters by extends option')` - Test --extends filter
- `it('filters by doesnt-extend option')` - Test --doesnt-extend filter
- `it('filters by implements option with single interface')` - Test --implements
- `it('filters by implements option with multiple interfaces')` - Test comma-separated
- `it('filters by doesnt-implement option')` - Test --doesnt-implement
- `it('filters by uses option with single trait')` - Test --uses
- `it('filters by uses option with multiple traits')` - Test comma-separated traits
- `it('filters by doesnt-use option')` - Test --doesnt-use
- `it('applies dsl query option')` - Test --query
- `it('applies dsl query from file option')` - Test --query-file
- `it('outputs json format')` - Test --format=json
- `it('outputs table format')` - Test --format=table
- `it('outputs csv format')` - Test --format=csv
- `it('outputs raw format')` - Test --format=raw
- `it('outputs jsonl format')` - Test --format=jsonl
- `it('outputs compact json')` - Test --compact
- `it('applies offset and limit')` - Test pagination
- `it('outputs count only')` - Test --count
- `it('selects specific fields')` - Test --fields
- `it('returns error on failure')` - Test error handling

### IntrospectRoutesCommandTest.php
Tests for `Mateffy\Introspect\Commands\IntrospectRoutesCommand`:

- `it('executes with no options')` - Test basic execution
- `it('filters by name option')` - Test --name filter
- `it('filters by name-not option')` - Test --name-not filter
- `it('filters by name-starts option')` - Test --name-starts filter
- `it('filters by name-ends option')` - Test --name-ends filter
- `it('filters by name-contains option')` - Test --name-contains filter
- `it('filters by path option')` - Test --path filter
- `it('filters by path-not option')` - Test --path-not filter
- `it('filters by path-starts option')` - Test --path-starts filter
- `it('filters by path-ends option')` - Test --path-ends filter
- `it('filters by path-contains option')` - Test --path-contains filter
- `it('filters by controller option')` - Test --controller filter
- `it('filters by controller with method option')` - Test --controller + --controller-method
- `it('filters by middleware option')` - Test --middleware filter
- `it('filters by middleware-all option')` - Test --middleware-all
- `it('filters by middleware-any option')` - Test --middleware-any
- `it('filters by method option')` - Test --method filter
- `it('filters by methods option')` - Test --methods filter
- `it('filters by has-param option')` - Test --has-param filter
- `it('filters by has-params option')` - Test --has-params filter
- `it('applies dsl query option')` - Test --query
- `it('applies dsl query from file option')` - Test --query-file
- `it('outputs json format')` - Test --format=json
- `it('outputs table format')` - Test --format=table
- `it('outputs csv format')` - Test --format=csv
- `it('outputs raw format')` - Test --format=raw
- `it('outputs jsonl format')` - Test --format=jsonl
- `it('outputs compact json')` - Test --compact
- `it('applies offset and limit')` - Test pagination
- `it('outputs count only')` - Test --count
- `it('selects specific fields')` - Test --fields
- `it('transforms route to array correctly')` - Test transformRoute()
- `it('includes route name in transformed output')` - Test name field
- `it('includes route path in transformed output')` - Test path field
- `it('includes route methods in transformed output')` - Test methods field
- `it('includes route controller in transformed output')` - Test controller field
- `it('includes route middleware in transformed output')` - Test middleware field
- `it('includes route parameters in transformed output')` - Test parameters field
- `it('includes route domain in transformed output')` - Test domain field
- `it('returns error on failure')` - Test error handling

### IntrospectViewsCommandTest.php
Tests for `Mateffy\Introspect\Commands\IntrospectViewsCommand`:

- `it('executes with no options')` - Test basic execution
- `it('filters by name option')` - Test --name filter
- `it('filters by name-not option')` - Test --name-not filter
- `it('filters by name-starts option')` - Test --name-starts filter
- `it('filters by name-ends option')` - Test --name-ends filter
- `it('filters by name-contains option')` - Test --name-contains filter
- `it('filters by uses option')` - Test --uses filter
- `it('filters by used-by option')` - Test --used-by filter
- `it('applies dsl query option')` - Test --query
- `it('applies dsl query from file option')` - Test --query-file
- `it('outputs json format')` - Test --format=json
- `it('outputs table format')` - Test --format=table
- `it('outputs csv format')` - Test --format=csv
- `it('outputs raw format')` - Test --format=raw
- `it('outputs jsonl format')` - Test --format=jsonl
- `it('outputs compact json')` - Test --compact
- `it('applies offset and limit')` - Test pagination
- `it('outputs count only')` - Test --count
- `it('selects specific fields')` - Test --fields
- `it('returns error on failure')` - Test error handling

### IntrospectCommandTest.php
Tests for `Mateffy\Introspect\Commands\IntrospectCommand`:

- `it('configures common options')` - Test configureCommonOptions()
- `it('applies pagination options')` - Test configureQuery()
- `it('reads DSL query from option')` - Test getDslQuery() with --query
- `it('reads DSL query from file')` - Test getDslQuery() with --query-file
- `it('throws error for non-existent query file')` - Test file error handling
- `it('outputs json results')` - Test outputJson()
- `it('outputs jsonl results')` - Test outputJsonl()
- `it('outputs table results')` - Test outputTable()
- `it('outputs csv results')` - Test outputCsv()
- `it('outputs raw results')` - Test outputRaw()
- `it('handles count-only mode')` - Test count output
- `it('selects specific fields')` - Test selectFields()
- `it('gets nested values with dot notation')` - Test getNestedValue()
- `it('formats table values')` - Test formatTableValue()
- `it('formats csv values')` - Test formatCsvValue()
- `it('escapes csv values')` - Test escapeCsv()

---

## Test Files Summary

| Category | Test Files | Test Cases | Est. Lines |
|----------|-----------|------------|------------|
| DTOs | 5 | ~40 | ~500 |
| Support | 3 | ~35 | ~400 |
| Reflection | 3 | ~35 | ~450 |
| Where Classes | 34 | ~200 | ~2500 |
| Query Components | 2 | ~20 | ~250 |
| View Analysis | 1 | ~15 | ~200 |
| Core Classes | 3 | ~25 | ~300 |
| Index | 2 | ~10 | ~150 |
| Commands | 7 | ~200 | ~1500 |
| **TOTAL** | **60** | **~580** | **~6250** |

---

## Implementation Order

1. **DTOs** - Simple value objects, good starting point
2. **Support Classes** - Core utilities used everywhere
3. **Reflection** - Foundation for model/class introspection
4. **View Analysis** - Complex but standalone
5. **Query Components** - Small but critical
6. **Core Classes** - Main API surface
7. **Index** - Database/storage functionality
8. **Commands** - CLI integration tests (7 files!)
9. **Where Classes** - Largest category, depends on understanding other parts

---

## Verification Steps

After implementation, run:
```bash
./vendor/bin/pest --coverage
```

Target metrics:
- **Line Coverage**: >90%
- **Function Coverage**: >95%
- **Class Coverage**: 100%
