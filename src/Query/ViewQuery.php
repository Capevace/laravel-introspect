<?php

namespace Mateffy\Introspect\Query;

use Illuminate\Support\Collection;
use Illuminate\View\Factory;
use Mateffy\Introspect\DTO\ViewPath;
use Mateffy\Introspect\DTO\ViewRepository;
use Mateffy\Introspect\Query\Builder\WhereBuilder;
use Mateffy\Introspect\Query\Builder\WhereViews;
use Mateffy\Introspect\Query\Builder\WithPagination;
use Mateffy\Introspect\Query\Contracts\PaginationInterface;
use Mateffy\Introspect\Query\Contracts\QueryPerformerInterface;
use Mateffy\Introspect\Query\Contracts\ViewQueryInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class ViewQuery implements ViewQueryInterface, PaginationInterface, QueryPerformerInterface
{
    use WithPagination;
    use WhereBuilder;
    use WhereViews;

    public function __construct(
        protected string $path,
    )
    {
        $this->wheres = collect();
    }

    public function get(): Collection
    {
        /** @var Factory $viewFinder */
        $viewFinder = app('view')->getFinder();

        $allPaths = collect();

        foreach ($viewFinder->getPaths() as $path) {
            $path = realpath($path);

            if (!$path) {
                continue;
            }

            $allPaths->push(new ViewPath($path));
        }

        foreach ($viewFinder->getHints() as $namespace => $paths) {
            foreach ($paths as $path) {
                $path = realpath($path);

                if (!$path) {
                    continue;
                }

                $allPaths->push(new ViewPath($path, namespace: $namespace));
            }
        }

        $views = collect();

        // We now have a list of paths that we now look for .blade.php files inside of recursively.
        // The Finder doesnt support this, so we have to do it manually.
        // We don't reinvent the wheel, and instead use the Finder class from Symfony.

        $repositories = collect();

        foreach ($allPaths as $path) {
            $files = (new Finder())
                ->files()
                ->name('*.blade.php')
                ->in($path->path)
                ->ignoreDotFiles(true)
                ->ignoreVCS(true)
                ->ignoreVCSIgnored(true)
                ->ignoreUnreadableDirs()
                ->getIterator();

            $repositories->push(
                new ViewRepository(
                    views: collect($files)
                        ->map(fn (SplFileInfo $file) => str($file->getRelativePathname())
                            ->beforeLast('.blade.php')
                            ->replace('/', '.')
                            ->replace('\\', '.')
                            ->trim('.')
                            ->toString()
                        )
                        ->values(),
                    namespace: $path->namespace,
                )
            );
        }

        return $repositories
            ->flatMap(fn (ViewRepository $views) => $views->getViewsAsAbsoluteString())
            ->filter(fn(string $view) => $this->filterUsingQuery($view))
            ->values()
            ->map(fn (string $view) => $this->transformResult($view));
    }

    protected function transformResult(string $view): string
    {
        return $view;
    }

    public function filterUsingQuery(string $view): bool
    {
        return $this->wheres->every(fn (Where $where) => $where->filter($view));
    }
}
