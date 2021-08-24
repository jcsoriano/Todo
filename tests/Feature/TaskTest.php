<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TaskTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testIndex()
    {
        // setup
        $user = $this->createUser();
        
        // perform request
        $this->actingAs($user)
            ->get(route('tasks.index'))
            ->assertOk();
    }

    public function testStore()
    {
        // setup
        $user = $this->createUser();
        $data = $this->createRawTaskData();

        // perform request
        $this->actingAs($user)
            ->post(route('tasks.store'), $data)
            ->assertCreated();

        // database assertions
        $this->assertDatabaseHas('tasks', $data);
    }

    public function testUpdate()
    {
        // setup
        $user = $this->createUser();
        $task = $this->createTaskForUser($user);
        $data = $this->createRawTaskData();

        // perform request
        $this->actingAs($user)
            ->put(route('tasks.update', compact('task')), $data)
            ->assertOk();

        // assert that the task with ID has new data
        $data['id'] = $task->id;
        $this->assertDatabaseHas('tasks', $data);
    }

    public function testDestroy()
    {
        // setup
        $user = $this->createUser();
        $task = $this->createTaskForUser($user);

        // perform request
        $this->actingAs($user)
            ->delete(route('tasks.destroy', compact('task')))
            ->assertOk();

        // database assertions
        $this->assertSoftDeleted($task);
    }

    public function testCannotUpdateOtherUsersTasks()
    {
        // setup
        $user = $this->createUser();
        $task = $this->createTaskForUser($user);
        $anotherUser = $this->createUser();
        $data = $this->createRawTaskData();

        // perform request
        $this->actingAs($anotherUser)
            ->put(route('tasks.update', compact('task')), $data)
            ->assertForbidden();
    }

    public function testCannotDeleteOtherUsersTasks()
    {
        // setup
        $user = $this->createUser();
        $task = $this->createTaskForUser($user);
        $anotherUser = $this->createUser();

        // perform request
        $this->actingAs($anotherUser)
            ->delete(route('tasks.destroy', compact('task')))
            ->assertForbidden();
    }

    public function testCannotCreateIfNotLoggedIn()
    {
        $this->post('tasks')
            ->assertRedirect('/login');
    }

    public function testCannotSeeTasksIfNotLoggedIn()
    {
        $this->get('tasks')
            ->assertRedirect('/login');
    }

    protected function createUser(): User
    {
        return User::factory()->create();
    }

    protected function createTaskForUser(User $user): Task
    {
        return Task::factory()->create([
            'user_id' => $user->id,
        ]);
    }

    protected function createRawTaskData(): array
    {
        return Task::factory()->raw();
    }
}
