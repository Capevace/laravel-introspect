<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Workbench\App\Interfaces\AnotherInterface;
use Workbench\App\Traits\AnotherTrait;

class AnotherModel extends BaseModel implements AnotherInterface
{
    use AnotherTrait;

    protected $fillable = [
        'name',
        'appended_only',
    ];

    protected $hidden = [
        'hidden_only',
    ];

    protected $appends = [
        'appended_only',
        'name',
    ];

    public function anotherMethod(): void
    {
        // Implementation of AnotherInterface method
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(TestModel::class);
    }
}
