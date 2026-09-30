<?php

namespace App\Models;

use App\Enums\StatusFeed;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

class Feed extends Model
{
    protected $fillable = ['name', 'url'];

    #[Override]
    public function casts(): array
    {
        return [
            'status' => StatusFeed::class,
        ];
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }
}
