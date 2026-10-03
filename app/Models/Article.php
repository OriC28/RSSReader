<?php

namespace App\Models;

use App\Enums\CategoryArticle;
use App\Enums\StatusArticle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Article extends Model
{
    protected $guarded = [];

    public function casts(): array
    {
        return [
            'status' => StatusArticle::class,
            'category' => CategoryArticle::class,
            'published_at' => 'datetime',
        ];
    }

    public function feed(): BelongsTo
    {
        return $this->belongsTo(Feed::class);
    }

    public function newsletter(): BelongsTo
    {
        return $this->belongsTo(Newsletter::class);
    }
}
