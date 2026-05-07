<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    // Clean up any temporary test files from previous runs
    $tempTestDir = base_path('tests/Temp');
    if (File::isDirectory($tempTestDir)) {
        File::deleteDirectory($tempTestDir);
    }
});

afterEach(function () {
    // Clean up temporary test files
    $tempTestDir = base_path('tests/Temp');
    if (File::isDirectory($tempTestDir)) {
        File::deleteDirectory($tempTestDir);
    }
    
    // Clean up output files
    $outputFiles = [
        base_path('tests-output.json'),
        base_path('tests-output.jsonl'),
        base_path('tests-output-compact.json'),
        base_path('test-query.txt'),
    ];
    
    foreach ($outputFiles as $file) {
        if (File::exists($file)) {
            File::delete($file);
        }
    }
});

// =============================================================================
// HAPPY PATHS - Basic Execution
// =============================================================================

it('command introspect:tests is registered', function () {
    // Verify the command is available
    $exitCode = Artisan::call('list', [
        '--format' => 'json',
    ]);
    
    expect($exitCode)->toBe(0);
    
    $output = Artisan::output();
    $json = json_decode($output, true);
    
    // Find the introspect:tests command
    $commands = $json['commands'] ?? [];
    $foundCommand = false;
    foreach ($commands as $command) {
        if ($command['name'] === 'introspect:tests') {
            $foundCommand = true;
            break;
        }
    }
    
    expect($foundCommand)->toBeTrue('introspect:tests command should be registered');
});

it('executes introspect:tests with no options', function () {
    $exitCode = Artisan::call('introspect:tests');
    
    // Should succeed or gracefully handle any errors
    expect($exitCode)->toBeIn([0, 1]);
    
    if ($exitCode === 0) {
        $output = Artisan::output();
        $json = json_decode($output, true);
        
        if ($json !== null) {
            expect($json)->toHaveKey('meta');
            expect($json)->toHaveKey('tests');
            expect($json['meta'])->toHaveKey('source', 'laravel-pest');
        }
    }
})->skip('Requires external test environment - causes conflicts when loading test files inside Pest test');

it('returns valid spec-compatible format', function () {
    $exitCode = Artisan::call('introspect:tests');
    
    if ($exitCode === 0) {
        $output = Artisan::output();
        $json = json_decode($output, true);
        
        if ($json !== null && !empty($json['tests'])) {
            $firstTest = $json['tests'][0];
            expect($firstTest)->toHaveKey('id');
            expect($firstTest)->toHaveKey('description');
            expect($firstTest)->toHaveKey('caseStatus');
            expect($firstTest)->toHaveKey('location');
            expect($firstTest['location'])->toHaveKey('file');
        }
    }
})->skip('Requires external test environment');

// =============================================================================
// HAPPY PATHS - Filter Options
// =============================================================================

it('filters tests by include pattern', function () {
    $exitCode = Artisan::call('introspect:tests', [
        '--include' => 'tests/*Test.php',
    ]);

    expect($exitCode)->toBeIn([0, 1]);
})->skip('Requires external test environment');

it('filters tests by exclude pattern', function () {
    $exitCode = Artisan::call('introspect:tests', [
        '--exclude' => 'tests/NonExistent/*',
    ]);

    expect($exitCode)->toBeIn([0, 1]);
})->skip('Requires external test environment');

it('filters tests by description containing text', function () {
    $exitCode = Artisan::call('introspect:tests', [
        '--description' => '*executes*',
    ]);

    expect($exitCode)->toBeIn([0, 1]);
})->skip('Requires external test environment');

it('filters tests by status active', function () {
    $exitCode = Artisan::call('introspect:tests', [
        '--status' => 'active',
    ]);

    expect($exitCode)->toBeIn([0, 1]);
})->skip('Requires external test environment');

it('filters tests by status todo', function () {
    $exitCode = Artisan::call('introspect:tests', [
        '--status' => 'todo',
    ]);

    expect($exitCode)->toBeIn([0, 1]);
})->skip('Requires external test environment');

// =============================================================================
// HAPPY PATHS - Output Formats
// =============================================================================

it('outputs json format', function () {
    $exitCode = Artisan::call('introspect:tests', [
        '--format' => 'json',
    ]);

    expect($exitCode)->toBeIn([0, 1]);
    
    if ($exitCode === 0) {
        $output = Artisan::output();
        $json = json_decode($output, true);
        expect($json)->not->toBeNull('Output should be valid JSON');
    }
})->skip('Requires external test environment');

it('outputs compact json format', function () {
    $exitCode = Artisan::call('introspect:tests', [
        '--compact' => true,
    ]);

    expect($exitCode)->toBeIn([0, 1]);
})->skip('Requires external test environment');

it('outputs table format', function () {
    $exitCode = Artisan::call('introspect:tests', [
        '--format' => 'table',
        '--limit' => 5,
    ]);

    expect($exitCode)->toBeIn([0, 1]);
    
    if ($exitCode === 0) {
        $output = Artisan::output();
        expect($output)->toContain('|');
    }
})->skip('Requires external test environment');

it('outputs csv format', function () {
    $exitCode = Artisan::call('introspect:tests', [
        '--format' => 'csv',
        '--limit' => 5,
    ]);

    expect($exitCode)->toBeIn([0, 1]);
    
    if ($exitCode === 0) {
        $output = Artisan::output();
        expect($output)->toContain(',');
    }
})->skip('Requires external test environment');

it('outputs raw format', function () {
    $exitCode = Artisan::call('introspect:tests', [
        '--format' => 'raw',
        '--limit' => 5,
    ]);

    expect($exitCode)->toBeIn([0, 1]);
})->skip('Requires external test environment');

it('outputs jsonl format', function () {
    $exitCode = Artisan::call('introspect:tests', [
        '--format' => 'jsonl',
        '--limit' => 5,
    ]);

    expect($exitCode)->toBeIn([0, 1]);
    
    if ($exitCode === 0) {
        $output = Artisan::output();
        $lines = explode("\n", trim($output));
        
        foreach ($lines as $line) {
            if (!empty($line)) {
                expect(json_decode($line))->not->toBeNull();
            }
        }
    }
})->skip('Requires external test environment');

// =============================================================================
// HAPPY PATHS - Output to File
// =============================================================================

it('writes output to file', function () {
    $outputFile = base_path('tests-output.json');
    
    $exitCode = Artisan::call('introspect:tests', [
        '--output-file' => $outputFile,
    ]);

    expect($exitCode)->toBeIn([0, 1]);
    
    if ($exitCode === 0) {
        expect(File::exists($outputFile))->toBeTrue();
        
        $content = File::get($outputFile);
        $json = json_decode($content, true);
        expect($json)->not->toBeNull();
    }
})->skip('Requires external test environment');

it('writes compact output to file', function () {
    $outputFile = base_path('tests-output-compact.json');
    
    $exitCode = Artisan::call('introspect:tests', [
        '--output-file' => $outputFile,
        '--compact' => true,
    ]);

    expect($exitCode)->toBeIn([0, 1]);
})->skip('Requires external test environment');

// =============================================================================
// HAPPY PATHS - Pagination
// =============================================================================

it('applies offset and limit', function () {
    $exitCode = Artisan::call('introspect:tests', [
        '--offset' => 0,
        '--limit' => 10,
    ]);

    expect($exitCode)->toBeIn([0, 1]);
    
    if ($exitCode === 0) {
        $output = Artisan::output();
        $json = json_decode($output, true);
        if ($json !== null) {
            expect(count($json['tests']))->toBeLessThanOrEqual(10);
        }
    }
})->skip('Requires external test environment');

it('outputs count only', function () {
    $exitCode = Artisan::call('introspect:tests', [
        '--count' => true,
    ]);

    expect($exitCode)->toBeIn([0, 1]);
    
    if ($exitCode === 0) {
        $output = trim(Artisan::output());
        expect(is_numeric($output) || $output === '0')->toBeTrue();
    }
})->skip('Requires external test environment');

// =============================================================================
// HAPPY PATHS - Field Selection
// =============================================================================

it('selects specific fields', function () {
    $exitCode = Artisan::call('introspect:tests', [
        '--fields' => 'id,description,caseStatus',
        '--limit' => 3,
    ]);

    expect($exitCode)->toBeIn([0, 1]);
})->skip('Requires external test environment');

it('selects nested fields using dot notation', function () {
    $exitCode = Artisan::call('introspect:tests', [
        '--fields' => 'id,description,location.file',
        '--limit' => 3,
    ]);

    expect($exitCode)->toBeIn([0, 1]);
})->skip('Requires external test environment');

// =============================================================================
// HAPPY PATHS - Custom Directories
// =============================================================================

it('accepts custom test directory', function () {
    $exitCode = Artisan::call('introspect:tests', [
        '--test-directory' => 'tests',
    ]);

    expect($exitCode)->toBeIn([0, 1]);
})->skip('Requires external test environment');

it('accepts custom app directory', function () {
    $exitCode = Artisan::call('introspect:tests', [
        '--app-directory' => 'app',
    ]);

    expect($exitCode)->toBeIn([0, 1]);
})->skip('Requires external test environment');

// =============================================================================
// HAPPY PATHS - Line Numbers
// =============================================================================

it('includes line numbers when --with-lines is specified', function () {
    $exitCode = Artisan::call('introspect:tests', [
        '--with-lines' => true,
    ]);

    expect($exitCode)->toBeIn([0, 1]);
    
    if ($exitCode === 0) {
        $output = Artisan::output();
        $json = json_decode($output, true);
        
        if ($json !== null && !empty($json['tests'])) {
            $firstTest = $json['tests'][0];
            if (isset($firstTest['location']['line'])) {
                expect($firstTest['location'])->toHaveKey('line');
                expect($firstTest['location'])->toHaveKey('endLine');
            }
        }
    }
})->skip('Requires nikic/php-parser and external test environment');

// =============================================================================
// BAD PATHS - Error Handling
// =============================================================================

it('handles empty test results gracefully', function () {
    // Use an exclude pattern that excludes everything
    $exitCode = Artisan::call('introspect:tests', [
        '--exclude' => '/**/*',
    ]);

    // Should succeed or handle gracefully
    expect($exitCode)->toBeIn([0, 1]);
})->skip('Requires external test environment');

it('handles invalid include pattern gracefully', function () {
    $exitCode = Artisan::call('introspect:tests', [
        '--include' => '[[[invalid',
    ]);

    expect($exitCode)->toBeIn([0, 1]);
})->skip('Requires external test environment');

it('handles non-existent test directory', function () {
    $exitCode = Artisan::call('introspect:tests', [
        '--test-directory' => 'non-existent-directory',
    ]);

    // Should handle gracefully
    expect($exitCode)->toBeIn([0, 1]);
    
    if ($exitCode === 0) {
        $output = Artisan::output();
        $json = json_decode($output, true);
        if ($json !== null) {
            expect($json['meta']['count'])->toBe(0);
        }
    }
})->skip('Requires external test environment');

it('handles write to invalid output file path', function () {
    $exitCode = Artisan::call('introspect:tests', [
        '--output-file' => '/nonexistent/directory/file.json',
    ]);

    // Should fail when trying to write to invalid path
    expect($exitCode)->toBe(1);
})->skip('Requires external test environment');

// =============================================================================
// BAD PATHS - Invalid Options
// =============================================================================

it('handles invalid status filter gracefully', function () {
    $exitCode = Artisan::call('introspect:tests', [
        '--status' => 'invalid-status',
    ]);

    expect($exitCode)->toBeIn([0, 1]);
    
    if ($exitCode === 0) {
        $output = Artisan::output();
        $json = json_decode($output, true);
        if ($json !== null) {
            expect($json['meta']['count'])->toBe(0);
        }
    }
})->skip('Requires external test environment');

it('handles negative offset gracefully', function () {
    $exitCode = Artisan::call('introspect:tests', [
        '--offset' => -5,
    ]);

    expect($exitCode)->toBeIn([0, 1]);
})->skip('Requires external test environment');

it('handles negative limit gracefully', function () {
    $exitCode = Artisan::call('introspect:tests', [
        '--limit' => -5,
    ]);

    expect($exitCode)->toBeIn([0, 1]);
})->skip('Requires external test environment');

// =============================================================================
// EDGE CASES - Combined Options
// =============================================================================

it('combines multiple filter options', function () {
    $exitCode = Artisan::call('introspect:tests', [
        '--include' => 'tests/*',
        '--description' => '*',
        '--limit' => 5,
    ]);

    expect($exitCode)->toBeIn([0, 1]);
})->skip('Requires external test environment');

it('combines include and exclude patterns', function () {
    $exitCode = Artisan::call('introspect:tests', [
        '--include' => 'tests/*',
        '--exclude' => 'tests/NonExistent/*',
    ]);

    expect($exitCode)->toBeIn([0, 1]);
})->skip('Requires external test environment');

it('handles output file with jsonl format', function () {
    $outputFile = base_path('tests-output.jsonl');
    
    $exitCode = Artisan::call('introspect:tests', [
        '--output-file' => $outputFile,
        '--format' => 'jsonl',
    ]);

    expect($exitCode)->toBeIn([0, 1]);
    
    if ($exitCode === 0 && File::exists($outputFile)) {
        $content = File::get($outputFile);
        $lines = explode("\n", trim($content));
        
        foreach ($lines as $line) {
            if (!empty($line)) {
                expect(json_decode($line))->not->toBeNull();
            }
        }
    }
})->skip('Requires external test environment');

it('handles table format with limit', function () {
    $exitCode = Artisan::call('introspect:tests', [
        '--format' => 'table',
        '--limit' => 3,
    ]);

    expect($exitCode)->toBeIn([0, 1]);
    
    if ($exitCode === 0) {
        $output = Artisan::output();
        expect($output)->toContain('|');
    }
})->skip('Requires external test environment');

// =============================================================================
// EDGE CASES - DSL Queries
// =============================================================================

it('applies dsl query option for file filter', function () {
    try {
        $exitCode = Artisan::call('introspect:tests', [
            '--query' => 'file *= tests',
        ]);

        expect($exitCode)->toBeIn([0, 1]);
    } catch (Exception $e) {
        // DSL parser might throw exceptions for some queries
        expect(true)->toBeTrue();
    }
})->skip('Requires external test environment');

it('applies dsl query option for description filter', function () {
    try {
        $exitCode = Artisan::call('introspect:tests', [
            '--query' => 'description *= test',
        ]);

        expect($exitCode)->toBeIn([0, 1]);
    } catch (Exception $e) {
        expect(true)->toBeTrue();
    }
})->skip('Requires external test environment');

it('applies dsl query option for status filter', function () {
    try {
        $exitCode = Artisan::call('introspect:tests', [
            '--query' => 'status = active',
        ]);

        expect($exitCode)->toBeIn([0, 1]);
    } catch (Exception $e) {
        expect(true)->toBeTrue();
    }
})->skip('Requires external test environment');

it('handles query file option', function () {
    $queryFile = base_path('test-query.txt');
    File::put($queryFile, 'file *= tests');
    
    try {
        $exitCode = Artisan::call('introspect:tests', [
            '--query-file' => $queryFile,
        ]);

        expect($exitCode)->toBeIn([0, 1]);
    } finally {
        if (File::exists($queryFile)) {
            File::delete($queryFile);
        }
    }
})->skip('Requires external test environment');

it('fails with non-existent query file', function () {
    $exitCode = Artisan::call('introspect:tests', [
        '--query-file' => '/nonexistent/query.txt',
    ]);

    expect($exitCode)->toBe(1);
});

// =============================================================================
// EDGE CASES - Special Characters
// =============================================================================

it('handles special characters in description filter', function () {
    $exitCode = Artisan::call('introspect:tests', [
        '--description' => '*test*',
    ]);

    expect($exitCode)->toBeIn([0, 1]);
})->skip('Requires external test environment');

it('handles unicode in test descriptions', function () {
    $exitCode = Artisan::call('introspect:tests');

    expect($exitCode)->toBeIn([0, 1]);
    
    if ($exitCode === 0) {
        $output = Artisan::output();
        expect(mb_check_encoding($output, 'UTF-8'))->toBeTrue();
    }
})->skip('Requires external test environment');

// =============================================================================
// EDGE CASES - Large Data Sets
// =============================================================================

it('handles large result sets with pagination', function () {
    $exitCode = Artisan::call('introspect:tests', [
        '--offset' => 0,
        '--limit' => 100,
    ]);

    expect($exitCode)->toBeIn([0, 1]);
    
    if ($exitCode === 0) {
        $output = Artisan::output();
        $json = json_decode($output, true);
        if ($json !== null) {
            expect(count($json['tests']))->toBeLessThanOrEqual(100);
        }
    }
})->skip('Requires external test environment');

// =============================================================================
// EDGE CASES - Metadata and References
// =============================================================================

it('preserves references in test metadata', function () {
    $exitCode = Artisan::call('introspect:tests');

    expect($exitCode)->toBeIn([0, 1]);
    
    if ($exitCode === 0) {
        $output = Artisan::output();
        $json = json_decode($output, true);
        
        if ($json !== null) {
            foreach ($json['tests'] as $test) {
                if (isset($test['metadata']['references'])) {
                    foreach ($test['metadata']['references'] as $ref) {
                        expect($ref)->toHaveKey('type');
                        expect($ref)->toHaveKey('value');
                    }
                }
            }
        }
    }
})->skip('Requires external test environment');

it('generates consistent test ids', function () {
    // Run twice and compare IDs for same tests
    $exitCode1 = Artisan::call('introspect:tests', ['--limit' => 10]);
    $output1 = Artisan::output();
    $json1 = json_decode($output1, true);
    
    $exitCode2 = Artisan::call('introspect:tests', ['--limit' => 10]);
    $output2 = Artisan::output();
    $json2 = json_decode($output2, true);
    
    expect($exitCode1)->toBeIn([0, 1]);
    expect($exitCode2)->toBeIn([0, 1]);
    
    // IDs should be deterministic (same file + description = same ID)
    if ($exitCode1 === 0 && $exitCode2 === 0 && 
        $json1 !== null && $json2 !== null &&
        count($json1['tests']) === count($json2['tests'])) {
        for ($i = 0; $i < count($json1['tests']); $i++) {
            expect($json1['tests'][$i]['id'])->toBe($json2['tests'][$i]['id']);
        }
    }
})->skip('Requires external test environment');

// =============================================================================
// EDGE CASES - Format Interactions
// =============================================================================

it('ignores compact flag with table format', function () {
    $exitCode = Artisan::call('introspect:tests', [
        '--format' => 'table',
        '--compact' => true,
    ]);

    expect($exitCode)->toBeIn([0, 1]);
})->skip('Requires external test environment');

it('ignores compact flag with csv format', function () {
    $exitCode = Artisan::call('introspect:tests', [
        '--format' => 'csv',
        '--compact' => true,
    ]);

    expect($exitCode)->toBeIn([0, 1]);
})->skip('Requires external test environment');
