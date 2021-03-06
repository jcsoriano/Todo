<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveTaskRequest;
use App\Models\BurndownSnapshot;
use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tasks = Auth::user()->tasks;
        $burndown = $this->snapshots();
        return Inertia::render('Tasks/List', compact('tasks', 'burndown'));
    }

    public function snapshots()
    {
        $user = Auth::user();

        // get recent snapshots
        $recentSnapshots = $user->burndownSnapshots()
            ->recent()
            ->get(['minute', 'num_remaining', 'num_tasks']);
        
        // get the closest snapshot beyond 60 minutes
        $startingSnapshot = BurndownSnapshot::startingSnapshot($user);
        if ($startingSnapshot) {
            $recentSnapshots->prepend($startingSnapshot);
        }
        
        return $recentSnapshots;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SaveTaskRequest $request)
    {
        return [
            // create a task associated with the logged-in user
            'task' => Auth::user()
                ->tasks()
                ->create($request->validated()),
            // update burndown
            'burndown' => $this->snapshots(),
        ];
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function show(Task $task)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function edit(Task $task)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function update(SaveTaskRequest $request, Task $task)
    {
        // authorize
        $this->authorize('update', $task);

        // update
        $task->update($request->validated());

        // return snapshots
        return $this->snapshots();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function destroy(Task $task)
    {
        // authorize
        $this->authorize('delete', $task);

        // delete
        $task->delete();

        // return snapshots
        return $this->snapshots();
    }
}
