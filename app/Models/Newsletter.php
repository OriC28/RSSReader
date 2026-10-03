<?php

namespace App\Models;

use App\Enums\StatusNewsletter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Newsletter extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'status' => StatusNewsletter::class,
            'sent_at' => 'datetime',
        ];
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }
}
