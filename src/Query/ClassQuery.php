<?php

namespace Mateffy\Introspect\Query;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use JsonException;
use Mateffy\Introspect\LaravelIntrospect;
use Mateffy\Introspect\Query\Builder\WhereBuilder;
use Mateffy\Introspect\Query\Builder\WhereClasses;
use Mateffy\Introspect\Query\Builder\WithPagination;
use Mateffy\Introspect\Query\Contracts\ClassQueryInterface;
use Mateffy\Introspect\Query\Contracts\PaginationInterface;
use Mateffy\Introspect\Query\Contracts\QueryPerformerInterface;
use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflector\DefaultReflector;
use Roave\BetterReflection\SourceLocator\SourceStubber\ReflectionSourceStubber;
use Roave\BetterReflection\SourceLocator\Type\AggregateSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\Composer\Factory\MakeLocatorForComposerJsonAndInstalledJson;
use Roave\BetterReflection\SourceLocator\Type\DirectoriesSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\PhpInternalSourceLocator;
use Spatie\StructureDiscoverer\Discover;

class ClassQuery implements ClassQueryInterface, PaginationInterface, QueryPerformerInterface
{
    use WhereBuilder;
    use WhereClasses;
    use WithPagination;

    public function __construct(
        protected string $path,
        protected array $directories = LaravelIntrospect::DEFAULT_DIRECTORIES,
    ) {
        $this->wheres = collect();
    }

    public function createSubquery(): self
    {
        return new ClassQuery(path: $this->path, directories: $this->directories);
    }

    protected function transformResult(ReflectionClass $class): mixed
    {
        return $class->getName();
    }

    public function filterUsingQuery(ReflectionClass $class): bool
    {
        return $this->wheres
            ->every(fn (Where $where) => $where->filter($class));
    }

    /**
     * @throws JsonException
     */
    public function get(): Collection
    {
        if (count($this->directories) === 0) {
            $paths = collect([$this->path]);
        } else {
            $paths = collect($this->directories)
                ->map(fn (string $directory) => "{$this->path}/{$directory}")
                ->all();
        }

        $astLocator = (new BetterReflection)->astLocator();
        $reflector = new DefaultReflector(new AggregateSourceLocator([
            new DirectoriesSourceLocator($paths, $astLocator),
            (new MakeLocatorForComposerJsonAndInstalledJson)($this->path, $astLocator),
            new PhpInternalSourceLocator($astLocator, new ReflectionSourceStubber),
        ]));

        $discover = Discover::in(...$paths);

        return collect($discover->classes()->get())
            ->map(fn (string $class) => $reflector->reflectClass($class))
            ->filter(fn (ReflectionClass $class) => $this->filterUsingQuery($class))
            ->values()
            ->map(fn (ReflectionClass $class) => $this->transformResult($class));
    }

    public function paginate(int $limit, int $offset): LengthAwarePaginator
    {
        throw new \Exception('Not implemented');
    }
}
