<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BurndownSnapshot extends Model
{
    use HasFactory;

    protected $fillable = ['minute', 'num_remaining', 'num_tasks'];
    protected $casts = [
        'minute' => 'datetime',
    ];

    public function scopeRecent(Builder $query)
    {
        $query->where('minute', '>=', now()->subHour()->subMinute())
            ->orderBy('minute', 'asc');
    }

    /**
     * Gets the latest snapshot beyond 60 minutes
     */
    public static function startingSnapshot(User $user)
    {
        return $user->burndownSnapshots()
            ->where('minute', '<', now()->subHour())
            ->orderBy('minute', 'desc')
            ->first(['minute', 'num_remaining', 'num_tasks']);
    }
}
