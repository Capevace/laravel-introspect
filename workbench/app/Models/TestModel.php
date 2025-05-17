<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Workbench\App\Interfaces\TestInterface;
use Workbench\App\Traits\TestTrait;

/**
 * @property string|int|null $only_via_docblock
 * @property-read string|int|null $readonly_via_docblock
 * @property-write string|int|null $writeonly_via_docblock
 */
class TestModel extends BaseModel implements TestInterface
{
    use TestTrait;

    protected $guarded = [
        'only_via_guarded'
    ];

    public function test_method(): void
    {
        // Implementation of TestInterface method
    }

    public function testMethod(): void
    {

    }

    public function another2(): BelongsTo
    {
        return $this->belongsTo(AnotherModel::class);
    }
}
