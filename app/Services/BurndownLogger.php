<?php

namespace App\Services;

use App\Models\BurndownSnapshot;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;

class BurndownLogger
{
    public function log()
    {
        // get the user
        $user = Auth::user();

        // get the minute
        $minute = now()->format('Y-m-j H:i:00');

        // get a snapshot of the data
        $data = [
            'num_remaining' => $user->tasks()->remaining()->count(),
            'num_tasks' => $user->tasks()->count(),
        ];

        // create or update a user's burndown snapshot for this minute
        $user->burndownSnapshots()->updateOrCreate(compact('minute'), $data);
    }
}
