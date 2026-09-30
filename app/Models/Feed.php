<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Feed extends Model
{
    protected $fillable = ['name', 'url'];

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }
}
