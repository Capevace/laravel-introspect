<?php

namespace Mateffy\Introspect\Commands;

use Illuminate\Console\Command;
use Mateffy\Introspect\Facades\Introspect;
use Mateffy\Introspect\Query\Contracts\QueryPerformerInterface;
use Mateffy\Introspect\Query\Query;
use Mateffy\Introspect\Support\ControllableConsoleOutput;
use Mateffy\Introspect\Support\PaginatableConsoleOutput;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\form;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class IntrospectInteractiveCommand extends Command
{
    use ControllableConsoleOutput;
    use PaginatableConsoleOutput;

	protected $signature = 'introspect';

	protected $description = 'Introspect your application interactively';

	public function handle(): void
	{
        $type = select(
            label:'What do you want to introspect?',
            options: [
                'Views',
                'Routes',
                'Models',
                'Classes',
            ],
        );

        $query = match ($type) {
            'Views' => $this->buildViewQuery(),
        };

        $this->outputResults($query->get(), match ($type) {
            'Views' => fn (string $view) => [
                'Name' => $view,
            ],
        });
	}

    protected function buildViewQuery(): QueryPerformerInterface
    {
        $query = Introspect::views();

        while (true) {
            $type = select(
                label: 'Query',
                options: [
                    'Name Equals',
                    'Name Doesn\'t Equal',
                    'Used By',
                    'Not Used By',
                    'Uses',
                    'Doesn\'t Use',
                    'Skip'
                ],
                default: 'Name Equals',
                required: true
            );

            if ($type === 'Skip') {
                break;
            }

            $value = text(
                label: 'Value',
                placeholder: 'Enter the value you want to query for',
                required: true
            );

            $continue = confirm(
                label: 'Do you want to add another query?',
                default: false,
            );

            switch ($type) {
                case 'Name Equals':
                    $query->whereNameEquals($value);
                    break;
                case 'Name Doesn\'t Equal':
                    $query->whereNameDoesntEqual($value);
                    break;
                case 'Used By':
                    $query->whereUsedBy($value);
                    break;
                case 'Not Used By':
                    $query->whereNotUsedBy($value);
                    break;
                case 'Uses':
                    $query->whereUses($value);
                    break;
                case 'Doesn\'t Use':
                    $query->whereDoesntUse($value);
                    break;
                default:
                    $continue = false;
                    break;
            }

            if (!$continue) {
                break;
            }
        }

        return $query;
    }
}
