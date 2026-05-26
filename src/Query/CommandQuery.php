<?php

namespace Mateffy\Introspect\Query;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Collection;
use Mateffy\Introspect\DTO\Command;
use Mateffy\Introspect\Query\Builder\WhereBuilder;
use Mateffy\Introspect\Query\Builder\WhereCommands;
use Mateffy\Introspect\Query\Builder\WithPagination;
use Mateffy\Introspect\Query\Contracts\CommandQueryInterface;
use Mateffy\Introspect\Query\Contracts\PaginationInterface;
use Mateffy\Introspect\Query\Contracts\QueryPerformerInterface;

class CommandQuery implements PaginationInterface, QueryPerformerInterface, CommandQueryInterface
{
    use WhereBuilder;
    use WhereCommands;
    use WithPagination;

    public function __construct()
    {
        $this->wheres = collect();
    }

    public function createSubquery(): self
    {
        return new CommandQuery();
    }

    public function get(): Collection
    {
        $commands = collect(Artisan::all())
            ->map(fn ($command) => Command::fromSymfonyCommand($command))
            ->filter(fn (Command $command) => $this->filterUsingQuery($command))
            ->values()
            ->when($this->offset !== null, fn (Collection $collection) => $collection->skip($this->offset))
            ->when($this->limit !== null, fn (Collection $collection) => $collection->take($this->limit));

        return $commands;
    }

    public function filterUsingQuery(Command $command): bool
    {
        return $this->wheres->every(fn (Where $where) => $where->filter($command));
    }
}
