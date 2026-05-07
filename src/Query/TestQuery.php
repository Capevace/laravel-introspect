<?php

declare(strict_types=1);

namespace Mateffy\Introspect\Query;

use Illuminate\Support\Collection;
use Mateffy\Introspect\DTO\Test;
use Mateffy\Introspect\DTO\TestLocation;
use Mateffy\Introspect\DTO\TestMetadata;
use Mateffy\Introspect\DTO\TestReference;
use Mateffy\Introspect\Query\Builder\WhereBuilder;
use Mateffy\Introspect\Query\Builder\WhereTests;
use Mateffy\Introspect\Query\Builder\WithPagination;
use Mateffy\Introspect\Query\Contracts\PaginationInterface;
use Mateffy\Introspect\Query\Contracts\QueryPerformerInterface;
use Mateffy\Introspect\Query\Contracts\TestQueryInterface;
use Mateffy\Introspect\Query\Where\TestWhere;
use Pest\Factories\TestCaseFactory;
use Pest\Factories\TestCaseMethodFactory;
use Pest\TestSuite;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;
use SebastianBergmann\FileIterator\Facade as FileIterator;

/**
 * Query class for extracting and filtering Pest tests
 */
class TestQuery implements PaginationInterface, QueryPerformerInterface, TestQueryInterface
{
    use WhereBuilder;
    use WhereTests;
    use WithPagination;

    private string $testDirectory = 'tests';

    private string $appDirectory = 'app';

    private string $pattern = '*.php';

    private bool $withLineNumbers = false;

    private ?\PhpParser\Parser $parser = null;

    private ?NodeFinder $nodeFinder = null;

    /**
     * @var array<string, string>
     */
    private array $fileMap = [];

    public function __construct(
        protected string $basePath,
    ) {
        $this->wheres = collect();
    }

    public function createSubquery(): self
    {
        return new TestQuery(basePath: $this->basePath);
    }

    public function inTestDirectory(string $directory): static
    {
        $this->testDirectory = $directory;

        return $this;
    }

    public function inAppDirectory(string $directory): static
    {
        $this->appDirectory = $directory;

        return $this;
    }

    public function withLineNumbers(bool $withLineNumbers = true): static
    {
        $this->withLineNumbers = $withLineNumbers;

        if ($withLineNumbers && $this->parser === null) {
            $this->initParser();
        }

        return $this;
    }

    public function withPattern(string $pattern): static
    {
        $this->pattern = $pattern;

        return $this;
    }

    /**
     * Add a where clause to the query
     */
    public function addWhere(\Mateffy\Introspect\Query\Where\Where $where): static
    {
        $this->wheres->push($where);

        return $this;
    }

    /**
     * Execute the query and return matching tests
     *
     * @return Collection<int, Test>
     */
    public function get(): Collection
    {
        $tests = $this->extractTests();

        // Apply filters
        $filtered = $tests->filter(fn (Test $test) => $this->filterUsingQuery($test));

        // Apply pagination
        if ($this->offset !== null) {
            $filtered = $filtered->slice($this->offset);
        }

        if ($this->limit !== null) {
            $filtered = $filtered->take($this->limit);
        }

        return $filtered->values();
    }

    /**
     * Extract all tests from the application
     *
     * @return Collection<int, Test>
     */
    protected function extractTests(): Collection
    {
        // Check if Pest classes are available
        if (! class_exists(TestSuite::class)) {
            return collect();
        }

        // Check if we're running inside a Pest test (which causes conflicts)
        if (defined('__PEST_RUNNING__')) {
            return collect();
        }

        try {
            // Initialize TestSuite
            $testSuite = TestSuite::getInstance(
                $this->basePath,
                $this->testDirectory
            );

            // Load helper files first (Pest.php, Helpers.php, etc.)
            $this->loadHelperFiles($testSuite);

            // Scan and load test files
            $this->loadTestFiles($testSuite);

            // Extract test information
            return $this->convertToTestDtos($testSuite);
        } catch (\Throwable $e) {
            // If Pest is not available or fails, return empty collection
            return collect();
        }
    }

    /**
     * Load helper files (Pest.php, Helpers.php, Expectations.php, Datasets).
     */
    private function loadHelperFiles(TestSuite $testSuite): void
    {
        $testsPath = $testSuite->rootPath . DIRECTORY_SEPARATOR . $testSuite->testPath;

        // Load Pest.php
        $pestFile = $testsPath . DIRECTORY_SEPARATOR . 'Pest.php';
        if (file_exists($pestFile)) {
            include_once $pestFile;
        }

        // Load Helpers.php
        $helpersFile = $testsPath . DIRECTORY_SEPARATOR . 'Helpers.php';
        if (file_exists($helpersFile)) {
            include_once $helpersFile;
        }

        // Load Expectations.php
        $expectationsFile = $testsPath . DIRECTORY_SEPARATOR . 'Expectations.php';
        if (file_exists($expectationsFile)) {
            include_once $expectationsFile;
        }

        // Load Datasets directory
        $datasetsDir = $testsPath . DIRECTORY_SEPARATOR . 'Datasets';
        if (is_dir($datasetsDir)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($datasetsDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($iterator as $file) {
                if (str_ends_with($file->getPathname(), '.php')) {
                    include_once $file->getPathname();
                }
            }
        }

        // Also check for .datasets.php files
        try {
            $datasetsFiles = (new FileIterator)->getFilesAsArray($testsPath, '.datasets.php');
            foreach ($datasetsFiles as $file) {
                include_once $file;
            }
        } catch (\Throwable $e) {
            // Ignore errors from FileIterator
        }
    }

    /**
     * Load all test files to register them in the TestRepository.
     */
    private function loadTestFiles(TestSuite $testSuite): void
    {
        // 1. Scan traditional tests directory
        $testDirectory = $testSuite->rootPath . DIRECTORY_SEPARATOR . $testSuite->testPath;
        try {
            $testFiles = (new FileIterator)->getFilesAsArray($testDirectory, $this->pattern);

            foreach ($testFiles as $file) {
                if ($this->isNonTestFile($file)) {
                    continue;
                }

                $this->loadTestFileDirectly($testSuite, $file);
            }
        } catch (\Throwable $e) {
            // Ignore errors from FileIterator
        }

        // 2. Scan app directory for colocated .test.php files
        $appDirectory = $testSuite->rootPath . DIRECTORY_SEPARATOR . $this->appDirectory;
        $colocatedFiles = [];
        if (is_dir($appDirectory)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($appDirectory, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );
            foreach ($iterator as $file) {
                if (str_contains($file->getPathname(), '.test.')) {
                    $colocatedFiles[] = $file->getPathname();
                }
            }
        }

        foreach ($colocatedFiles as $file) {
            $this->loadTestFileDirectly($testSuite, $file);
        }
    }

    /**
     * Load a test file directly via include_once.
     * Tracks the real filename since Backtrace::testFile() returns wrong filename.
     */
    private function loadTestFileDirectly(TestSuite $testSuite, string $file): void
    {
        // Get descriptions before loading
        $descriptionsBefore = [];
        foreach ($testSuite->tests->getFilenames() as $filename) {
            $factory = $testSuite->tests->get($filename);
            if ($factory instanceof TestCaseFactory) {
                foreach ($factory->methods as $desc => $method) {
                    $descriptionsBefore[$desc] = true;
                }
            }
        }

        try {
            ob_start();
            include_once $file;
            ob_end_clean();
        } catch (\Throwable $e) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            // Skip files that fail to load
        }

        // Find newly added methods and map them to this file
        foreach ($testSuite->tests->getFilenames() as $filename) {
            $factory = $testSuite->tests->get($filename);
            if ($factory instanceof TestCaseFactory) {
                foreach ($factory->methods as $desc => $method) {
                    if (! isset($descriptionsBefore[$desc])) {
                        // This is a newly added method, map it to the real file
                        $this->fileMap[$desc] = $file;
                    }
                }
            }
        }
    }

    /**
     * Check if a file is a non-test helper/dataset file.
     */
    private function isNonTestFile(string $filename): bool
    {
        $basename = basename($filename);

        // If file has .test. in the name, it's a test file (e.g., Foo.test.php, Foo.test.unit.php)
        if (str_contains($basename, '.test.')) {
            return false;
        }

        // Skip common helper files
        $nonTestPatterns = [
            'Pest.php',
            'Helpers.php',
            'Expectations.php',
            'Datasets/',
            'CreatesApplication.php',
            'TestCase',
        ];

        foreach ($nonTestPatterns as $pattern) {
            if (str_contains($basename, $pattern) || str_contains($filename, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Convert Pest TestSuite data to Test DTOs
     *
     * @return Collection<int, Test>
     */
    private function convertToTestDtos(TestSuite $testSuite): Collection
    {
        $testRepository = $testSuite->tests;
        $tests = collect();

        // Get all test cases
        $reflection = new \ReflectionClass($testRepository);
        $property = $reflection->getProperty('testCases');
        $property->setAccessible(true);
        $testCases = $property->getValue($testRepository);

        foreach ($testCases as $testCaseFactory) {
            foreach ($testCaseFactory->methods as $description => $methodFactory) {
                // Use the fileMap to get the correct filename
                $actualFilename = $this->fileMap[$description] ?? $methodFactory->filename;
                $relativePath = str_replace($this->basePath . DIRECTORY_SEPARATOR, '', $actualFilename);

                $test = $this->convertMethodToTestDto(
                    $methodFactory,
                    $description,
                    $relativePath,
                    $testCaseFactory
                );

                $tests->push($test);
            }
        }

        return $tests;
    }

    /**
     * Convert a single Pest method factory to a Test DTO
     */
    private function convertMethodToTestDto(
        TestCaseMethodFactory $methodFactory,
        string $description,
        string $relativeFilePath,
        TestCaseFactory $testCaseFactory,
    ): Test {
        // Build references array
        $references = [];

        foreach ($methodFactory->issues ?? [] as $issue) {
            $references[] = new TestReference(
                type: 'issue',
                value: (string) $issue,
            );
        }

        foreach ($methodFactory->prs ?? [] as $pr) {
            $references[] = new TestReference(
                type: 'pr',
                value: (string) $pr,
            );
        }

        // Determine status
        $caseStatus = $methodFactory->todo ? 'todo' : 'active';

        // Get line numbers if enabled
        $line = null;
        $endLine = null;
        if ($this->withLineNumbers && $this->parser !== null) {
            $lines = $this->getMethodLines($relativeFilePath, $description);
            if ($lines !== null) {
                $line = $lines['line'];
                $endLine = $lines['endLine'];
            }
        }

        // Build location
        $location = new TestLocation(
            file: $relativeFilePath,
            line: $line,
            endLine: $endLine,
            methodName: $this->generateMethodName($description),
        );

        // Build metadata
        $custom = collect([
            'class' => $testCaseFactory->class,
            'traits' => $testCaseFactory->traits,
        ]);

        if (! empty($methodFactory->groups)) {
            $custom->put('groups', $methodFactory->groups);
        }

        if (! empty($methodFactory->assignees)) {
            $custom->put('assignees', $methodFactory->assignees);
        }

        if ($methodFactory->repetitions > 1) {
            $custom->put('repetitions', $methodFactory->repetitions);
        }

        if (! empty($methodFactory->depends)) {
            $custom->put('dependencies', $methodFactory->depends);
        }

        if (! empty($methodFactory->describing)) {
            $custom->put('describing', array_map(
                fn ($desc) => (string) $desc,
                $methodFactory->describing
            ));
        }

        // Notes - join array into single string
        $notes = null;
        if (! empty($methodFactory->notes)) {
            $notes = implode("\n", $methodFactory->notes);
        }

        // Tags from groups
        $tags = null;
        if (! empty($methodFactory->groups)) {
            $tags = $methodFactory->groups;
        }

        $metadata = new TestMetadata(
            notes: $notes,
            tags: $tags,
            references: $references,
            custom: $custom->isNotEmpty() ? $custom : null,
        );

        // Generate ID
        $id = Test::generateId($relativeFilePath, $description);

        return new Test(
            description: $description,
            location: $location,
            caseStatus: $caseStatus,
            executionStatus: null, // We don't know execution status from static analysis
            metadata: $metadata,
            id: $id,
        );
    }

    /**
     * Get line numbers for a test method in a file.
     *
     * @return array{line: int, endLine: int}|null
     */
    protected function getMethodLines(string $relativeFilePath, string $description): ?array
    {
        if ($this->parser === null || $this->nodeFinder === null) {
            return null;
        }

        $absolutePath = $this->basePath . DIRECTORY_SEPARATOR . $relativeFilePath;

        if (! file_exists($absolutePath)) {
            return null;
        }

        try {
            $code = file_get_contents($absolutePath);
            $ast = $this->parser->parse($code);

            if ($ast === null) {
                return null;
            }

            // Look for it() or test() calls with the matching description
            $finder = $this->nodeFinder;

            // Find all function calls
            $calls = $finder->findInstanceOf($ast, \PhpParser\Node\Expr\FuncCall::class);

            foreach ($calls as $call) {
                $funcName = '';
                if ($call->name instanceof \PhpParser\Node\Name) {
                    $funcName = $call->name->getLast();
                }

                // Check if it's it() or test()
                if (! in_array($funcName, ['it', 'test'], true)) {
                    continue;
                }

                // Get first argument (description)
                if (empty($call->args)) {
                    continue;
                }

                $firstArg = $call->args[0];
                if (! $firstArg instanceof \PhpParser\Node\Arg) {
                    continue;
                }

                $argValue = '';
                if ($firstArg->value instanceof \PhpParser\Node\Scalar\String_) {
                    $argValue = $firstArg->value->value;
                }

                // Match the description
                // Pest adds "it " or "test " prefix to descriptions, and may add describe() prefixes
                // Format can be: "it description", "test description", "`name` → it description", etc.
                // We check if the description ends with the argValue (with optional "it " or "test " prefix)
                $possibleEndings = [
                    $argValue,
                    'it ' . $argValue,
                    'test ' . $argValue,
                ];
                
                $matched = false;
                foreach ($possibleEndings as $ending) {
                    if ($description === $ending || str_ends_with($description, $ending)) {
                        $matched = true;
                        break;
                    }
                }
                
                if ($matched) {
                    return [
                        'line' => $call->getStartLine(),
                        'endLine' => $call->getEndLine(),
                    ];
                }
            }
        } catch (\Throwable $e) {
            // Silently fail - line numbers are optional
        }

        return null;
    }

    /**
     * Generate the evaluable method name from a description.
     */
    private function generateMethodName(?string $description): ?string
    {
        if ($description === null) {
            return null;
        }

        // Convert to evaluable format (similar to Str::evaluable in Pest)
        $methodName = preg_replace('/[^a-zA-Z0-9_]/', '_', $description);
        $methodName = preg_replace('/_+/', '_', $methodName);
        $methodName = trim($methodName, '_');

        return 'test_' . $methodName;
    }

    /**
     * Initialize PHP parser for line number extraction
     */
    protected function initParser(): void
    {
        if (! class_exists(ParserFactory::class)) {
            return;
        }

        try {
            $this->parser = (new ParserFactory)->createForHostVersion();
            $this->nodeFinder = new NodeFinder;
        } catch (\Throwable $e) {
            // Parser not available, line numbers will be skipped
            $this->parser = null;
            $this->nodeFinder = null;
        }
    }

    /**
     * Filter tests using the registered where clauses
     */
    protected function filterUsingQuery(Test $test): bool
    {
        /** @var TestWhere $where */
        foreach ($this->wheres as $where) {
            if (! $where->filter($test)) {
                return false;
            }
        }

        return true;
    }
}
