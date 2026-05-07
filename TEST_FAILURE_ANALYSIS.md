# Test Failure Analysis

## Summary
- **Total Test Suites**: 127
- **Failing Tests**: 111 individual tests across multiple suites
- **Passing Tests**: 724
- **Skipped**: 23

---

## 1. ViewQueryTest (39 failures) - PRE-EXISTING

**File**: `tests/ViewQueryTest.php`

**Root Cause**: Hardcoded expected view counts don't match actual fixture count

**Details**:
- Expected: 32 total views (based on hardcoded `totalViews = 32`)
- Actual: 48 total views found in system
- Difference: +16 views (likely added since tests were written)

**Error Pattern**:
```
Failed asserting that actual size 48 matches expected size 32.
Failed asserting that actual size 47 matches expected size 31.
```

**Affected Tests**: All view query tests with count assertions, including:
- `it can query all views`
- `it can offset views`  
- `it can query by name with` (36 parameterized tests)

**Fix Required**: Update hardcoded counts in `with()` data provider arrays to reflect actual fixture count of 48 views instead of 32.

---

## 2. ServiceProviderTest (6 failures) - TEST ISSUE

**File**: `tests/Unit/LaravelIntrospectServiceProviderTest.php`

**Root Cause**: Tests attempt to resolve Query classes directly from container without required dependencies

**Error Pattern**:
```
BindingResolutionException: Unresolvable dependency resolving [Parameter #0 [ <required> string $path ]] 
in class Mateffy\Introspect\Query\ClassQuery
```

**Affected Tests**:
- `it registers all query interface bindings`
- `it binds ClassQueryInterface to ClassQuery`
- `it binds ModelQueryInterface to ModelQuery`
- `it binds RouteQueryInterface to RouteQuery`
- `it can resolve query interfaces after boot`
- `it bindings are singletons or instances`

**Why It Fails**:
Query classes (ClassQuery, RouteQuery, ModelQuery) require a `$path` parameter in constructor:
```php
public function __construct(string $path, ...)
```

Laravel's container cannot auto-resolve a primitive `string` type without configuration.

**Fix Required**: 
- Option A: Don't test direct container resolution - use the LaravelIntrospect facade instead
- Option B: Mock the queries or register bindings in test setup
- Option C: Skip these tests as they test implementation details not public API

---

## 3. NestedWhereTest (4 failures) - MISSING IMPLEMENTATION

**File**: `tests/Unit/Query/NestedWhereTest.php`

**Root Cause**: Where classes don't implement `toArray()` and `fromArray()` methods required by serialization

**Error Patterns**:
```
Error: Call to undefined method Mateffy\Introspect\Query\Where\Views\WhereViewNameEquals::toArray()

ArgumentCountError: Mateffy\Introspect\Query\Where\NestedWhere::__construct(): 
Argument #1 ($wheres) not passed
```

**Affected Tests**:
- `it serializes to array`
- `it deserializes from array` (2 tests)
- `it handles serialization roundtrip`

**Why It Fails**:
Only `NestedWhere` has `toArray()` method that calls `toArray()` on child where clauses:
```php
// src/Query/Where/NestedWhere.php:51
'wheres' => Arr::map($this->wheres, fn ($where) => $where->toArray()),
```

But `WhereViewNameEquals`, `WhereClassNameEquals`, etc. don't have `toArray()` methods.

**Fix Required**: 
- **Option A**: Add `toArray()` and `fromArray()` to ALL Where classes
- **Option B**: Remove serialization tests (if serialization not needed)
- **Option C**: Make NestedWhere's `toArray()` check if method exists before calling

---

## 4. WhereSerializerTest (6 failures) - MISSING IMPLEMENTATION

**File**: `tests/Unit/Query/WhereSerializerTest.php`

**Root Cause**: WhereSerializer expects `fromArray()` methods that don't exist on Where classes

**Error Patterns**:
```
Error: Call to undefined method Mateffy\Introspect\Query\Where\Classes\WhereExtendsClass::fromArray()

InvalidArgumentException: Unknown where type: view-name-equals
```

**Affected Tests**:
- `it deserializes nested where clauses`
- `it preserves all flag during class extends deserialization`
- `it preserves not flag during deserialization`
- `it deserializes view name equals where`
- `it deserializes class implements where`

**Why It Fails**:
WhereSerializer tries to call `fromArray()` on Where classes:
```php
// src/Query/Where/WhereSerializer.php:45
return $class::fromArray($data);
```

But most Where classes don't have this static factory method. Also, the type mapping in `deserialize()` is incomplete:
```php
// WhereSerializer.php:17-18
'default' => throw new InvalidArgumentException('Unknown where type: ' . $data['type']),
```

**Fix Required**:
- Add `fromArray()` static method to all Where classes
- Expand the type mapping in WhereSerializer to handle all where types
- Add view-*, route-*, model-*, class-* type mappings

---

## 5. FacadeTest (3 failures) - TEST ISSUE

**File**: `tests/Unit/IntrospectFacadeTest.php`

**Root Cause**: Tests have incorrect assertions or wrong expectations

**Error Patterns**:
```
ArgumentCountError: Too few arguments to function Pest\Mixins\Expectation::toThrow(), 0 passed

Failed asserting that two objects are equal. (different paths)

Error: Call to undefined method Mateffy\Introspect\LaravelIntrospect::getFacadeAccessor()
```

**Affected Tests**:
- `it can be called statically` - `toThrow()` called without exception class
- `it helper function returns same instance as facade` - different base paths
- `it returns correct facade accessor` - calling instance method statically

**Why It Fails**:
1. `Introspect::views()->get()` works fine, but test uses wrong Pest syntax
2. Helper function uses project root (`/Users/mat/dev/laravel-introspect`), facade uses testbench path
3. `getFacadeAccessor()` is defined on the Facade class, not the underlying class

**Fix Required**:
- Fix Pest syntax: `expect(fn () => ...)->not->toThrow(Exception::class)`
- Accept different paths or standardize initialization
- Call `Introspect::getFacadeAccessor()` not on instance

---

## 6. Command Tests (18+ failures) - OUTPUT FORMAT ISSUES

**Files**: `tests/Feature/Commands/*.php`

**Root Cause**: JSON output format tests expect "meta" and "data" keys but get different output

**Error Pattern**:
```
Expected: []
To contain: "meta"
```

**Affected Tests**:
- `IntrospectRoutesCommandTest > it outputs json format`
- `IntrospectModelsCommandTest > it outputs json format`
- `IntrospectSchemaCommandTest > it outputs json format`

**Why It Fails**:
When no routes/models match the criteria, command outputs `[]` (empty array) but tests expect a JSON structure with meta/data keys.

**Fix Required**:
- Update tests to handle empty results case properly
- Or ensure JSON format always wraps results with meta/data structure

---

## 7. Model DTO Test (6 failures) - IMPLEMENTATION BEHAVIOR

**File**: `tests/Unit/Dto/ModelTest.php`

**Root Cause**: Model::schema() implementation behaves differently than test expectations

**Error Patterns**:
```
Failed asserting that an array has the key 'secret'
Failed asserting that an array has the key 'id'
ErrorException: Undefined array key "status"
```

**Affected Tests**:
- `it marks write-only properties correctly in schema`
- `it filters out properties with non-standard types from schema`
- `it generates schema with single type when not supporting multiple types`
- `it required list is empty when all properties are nullable`
- `it includes default values in schema when present`
- `it all properties become required when none are nullable`

**Why It Fails**:
The `schema()` method has complex logic for:
- Filtering properties by type
- Marking readOnly/writeOnly
- Setting required fields
- Handling defaults

Tests expect specific behavior but implementation does something different.

**Fix Required**:
- Align test expectations with actual implementation behavior
- OR update implementation to match documented test expectations

---

## 8. ControllableConsoleOutputTest (2 failures) - MOCKING ISSUE

**File**: `tests/Unit/Support/ControllableConsoleOutputTest.php`

**Root Cause**: Mock objects don't satisfy Laravel's type requirements

**Error Patterns**:
```
TypeError: Illuminate\Console\Command::setOutput(): 
Argument #1 ($output) must be of type Illuminate\Console\OutputStyle, MockObject_OutputInterface_f983ab83 given

Error: Call to a member function hasOption() on null
```

**Affected Tests**:
- `it outputs results as json when format is json`
- `it handles format option check`

**Why It Fails**:
Laravel's Command class requires specific `OutputStyle` type, not generic mock. Also, Command needs proper input definition to check options.

**Fix Required**:
- Use proper Laravel test helpers ( `artisan()` ) instead of mocking
- Or skip unit testing of trait and rely on integration tests

---

## 9. RegexHelperTest (2 failures) - EDGE CASES

**File**: `tests/Unit/Support/RegexHelperTest.php`

**Root Cause**: Edge cases not handled by implementation

**Error Patterns**:
```
Failed asserting that two strings are identical.
-'Namespace\ClassName'
+'Namespace\\ClassName'

Failed asserting that false is true.
```

**Affected Tests**:
- `it handles patterns with backslashes` - double escaping issue
- `it handles empty needle pattern` - empty string returns false

**Why It Fails**:
- Backslashes need to be escaped in regex, but implementation doubles them
- Empty string pattern should match nothing but doesn't

**Fix Required**:
- Update RegexHelper to handle backslashes properly
- Add special case for empty pattern

---

## 10. RouteTest (1 failure) - TEST DATA ISSUE

**File**: `tests/Unit/Dto/RouteTest.php`

**Root Cause**: Test expects invokable controller to have specific action format

**Affected Test**: `it can create a route DTO from an invokable controller`

**Why It Fails**:
Route DTO may format invokable controller actions differently than expected in test.

**Fix Required**:
- Update test expectation to match actual behavior
- Or fix Route DTO to format invokable actions consistently

---

## Failure Categories Summary

### By Root Cause:

| Category | Count | Fix Type |
|----------|-------|----------|
| Hardcoded counts (ViewQueryTest) | 39 | Update test data |
| Container resolution (ServiceProvider) | 6 | Fix test approach |
| Missing toArray/fromArray (NestedWhere/Serializer) | 10 | Add implementation |
| Incorrect test assertions (Facade/Other) | 10 | Fix test code |
| JSON output format (Commands) | 18+ | Fix test expectations |
| Schema behavior (Model DTO) | 6 | Align test/implementation |
| Mocking issues (ConsoleOutput) | 2 | Use proper Laravel helpers |
| Edge cases (RegexHelper) | 2 | Fix implementation |

### By Fix Complexity:

**Easy (test updates only)**: ~65 tests
- ViewQueryTest: Update hardcoded 32→48
- FacadeTest: Fix assertions  
- Command tests: Adjust JSON expectations
- ServiceProvider: Change test approach

**Medium (add toArray/fromArray)**: ~10 tests
- Add toArray() to all Where classes
- Add fromArray() to all Where classes
- Update WhereSerializer type mappings

**Hard (implementation changes)**: ~8 tests
- RegexHelper backslash handling
- Model schema behavior alignment
- Console output mocking

---

## Recommendation

**Priority 1 (Quick Wins)**: Fix test assertion issues and update hardcoded counts
- 65+ tests will pass with simple updates

**Priority 2 (Implementation)**: Add serialization support to Where classes
- Required for NestedWhere serialization features
- ~10 tests affected

**Priority 3 (Edge Cases)**: Fix RegexHelper and Model schema edge cases
- ~8 tests affected
- Lower priority as they test edge behavior

**Priority 4 (Skip)**: Consider skipping some ServiceProvider tests
- Tests implementation details, not public API
- Low value for test coverage
