<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['is_done', 'name'];
    protected $casts = [
        'is_done' => 'boolean',
    ];

    public function scopeRemaining(Builder $query)
    {
        $query->where('is_done', false);
    }
}
