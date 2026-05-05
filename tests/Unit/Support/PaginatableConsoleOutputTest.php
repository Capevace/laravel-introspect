<?php

use Illuminate\Console\Command;
use Mateffy\Introspect\Query\Contracts\PaginationInterface;
use Mateffy\Introspect\Support\PaginatableConsoleOutput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

// Create a mock pagination query
class MockPaginationQuery implements PaginationInterface
{
    public ?int $offset = null;

    public ?int $limit = null;

    public function offset(int $offset): static
    {
        $this->offset = $offset;

        return $this;
    }

    public function limit(int $limit): static
    {
        $this->limit = $limit;

        return $this;
    }
}

// Create a test command class that uses the trait
class TestPaginatableCommand extends Command
{
    use PaginatableConsoleOutput;

    protected $signature = 'test:paginate {--offset=} {--limit=}';

    public function testPaginate(PaginationInterface $query): PaginationInterface
    {
        return $this->paginate($query);
    }
}

// Helper to create properly configured command input
function createPaginateCommand(array $options): TestPaginatableCommand
{
    $command = new TestPaginatableCommand;

    // Create input definition with options
    $definition = new InputDefinition([
        new InputOption('offset', null, InputOption::VALUE_OPTIONAL, '', null),
        new InputOption('limit', null, InputOption::VALUE_OPTIONAL, '', null),
    ]);

    $input = new ArrayInput($options, $definition);
    $input->setInteractive(false);

    $command->setInput($input);

    return $command;
}

it('applies offset to query', function () {
    $command = createPaginateCommand(['--offset' => 10]);

    $query = new MockPaginationQuery;
    $result = $command->testPaginate($query);

    expect($result->offset)->toBe(10);
});

it('applies limit to query', function () {
    $command = createPaginateCommand(['--limit' => 25]);

    $query = new MockPaginationQuery;
    $result = $command->testPaginate($query);

    expect($result->limit)->toBe(25);
});

it('applies both offset and limit', function () {
    $command = createPaginateCommand([
        '--offset' => 5,
        '--limit' => 20,
    ]);

    $query = new MockPaginationQuery;
    $result = $command->testPaginate($query);

    expect($result->offset)->toBe(5)
        ->and($result->limit)->toBe(20);
});

it('returns unchanged query when no pagination options', function () {
    $command = createPaginateCommand([]);

    $query = new MockPaginationQuery;
    $result = $command->testPaginate($query);

    expect($result->offset)->toBeNull()
        ->and($result->limit)->toBeNull();
});

it('converts string options to integers', function () {
    $command = createPaginateCommand([
        '--offset' => '15',
        '--limit' => '30',
    ]);

    $query = new MockPaginationQuery;
    $result = $command->testPaginate($query);

    expect($result->offset)->toBe(15)
        ->and($result->offset)->toBeInt()
        ->and($result->limit)->toBe(30)
        ->and($result->limit)->toBeInt();
});

it('has paginate method available', function () {
    $command = new TestPaginatableCommand;

    expect(method_exists($command, 'paginate'))->toBeTrue();
});

it('accepts PaginationInterface parameter', function () {
    $command = new TestPaginatableCommand;

    $reflection = new ReflectionMethod($command, 'paginate');
    $params = $reflection->getParameters();

    $type = $params[0]->getType();
    expect($type->getName())->toBe(PaginationInterface::class);
});

it('returns PaginationInterface', function () {
    $command = new TestPaginatableCommand;

    $reflection = new ReflectionMethod($command, 'paginate');
    $returnType = $reflection->getReturnType();

    expect($returnType->getName())->toBe(PaginationInterface::class);
});

it('handles zero offset', function () {
    $command = createPaginateCommand(['--offset' => 0]);

    $query = new MockPaginationQuery;
    $result = $command->testPaginate($query);

    expect($result->offset)->toBe(0);
});

it('handles zero limit', function () {
    $command = createPaginateCommand(['--limit' => 0]);

    $query = new MockPaginationQuery;
    $result = $command->testPaginate($query);

    expect($result->limit)->toBe(0);
});

it('handles only offset set', function () {
    $command = createPaginateCommand(['--offset' => 50]);

    $query = new MockPaginationQuery;
    $result = $command->testPaginate($query);

    expect($result->offset)->toBe(50)
        ->and($result->limit)->toBeNull();
});

it('handles only limit set', function () {
    $command = createPaginateCommand(['--limit' => 10]);

    $query = new MockPaginationQuery;
    $result = $command->testPaginate($query);

    expect($result->offset)->toBeNull()
        ->and($result->limit)->toBe(10);
});

it('preserves query instance', function () {
    $command = createPaginateCommand(['--limit' => 5]);

    $query = new MockPaginationQuery;
    $result = $command->testPaginate($query);

    expect($result)->toBe($query);
});
