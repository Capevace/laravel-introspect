<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BaseModel extends Model
{
    // Base model implementation

    protected $fillable = [
        'nested_not_overridden',
    ];

    public function another(): BelongsTo
    {
        return $this->belongsTo(AnotherModel::class);
    }
}
