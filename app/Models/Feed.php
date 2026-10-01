<?php

namespace App\Models;

use App\Enums\StatusFeed;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

class Feed extends Model
{
    protected $guarded = [];

    #[Override]
    public function casts(): array
    {
        return [
            'status' => StatusFeed::class,
            'last_fetched_at' => 'datetime',
            'last_error_at' => 'datetime',
        ];
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }
}
