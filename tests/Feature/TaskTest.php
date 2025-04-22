<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;


class TaskTest extends TestCase {
    use RefreshDatabase;

    /** @test */
    public function a_user_can_create_a_task() {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => bcrypt('password')
        ]); 

        $taskData = [
            'description' => 'This is a test task description.'
        ];

        $response = $this->actingAs($user)->post(route('tasks.store'), $taskData);
        $response->assertRedirect(route('tasks.index'));

        $this->assertDatabaseHas('tasks', $taskData);
    }



    /** @test */
    public function a_user_can_delete_a_task() {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => bcrypt('password')
        ]);

        $task = Task::create([
            'description' => 'This is a task to be deleted.',
            'user_id' => $user->id
        ]);

        $response = $this->actingAs($user)->delete(route('tasks.destroy', $task->id));
        $response->assertRedirect(route('tasks.index'));

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }



    /** @test */
    public function a_user_can_mark_a_task_as_completed() {
        $user = User::factory()->create();

        $task = Task::create([
            'description' => 'Finished this task',
            'user_id' => $user->id,
            'completed'=>false,
        ]);

        $response = $this->actingAs($user)->patch(route('tasks.update-completed', $task->id),[
            'completed' => true,
        ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'completed' => true,
        ]);
    }



    /** @test */
    public function a_user_can_unMark_a_task_as_uncompleted() {
        $user = User::factory()->create();

        $task = Task::create([
            'description' => 'Havnet finished this task',
            'user_id' => $user->id,
            'completed'=>true,
        ]);

        $response = $this->actingAs($user)->patch(route('tasks.update-completed', $task->id),[
            'completed' => false,
        ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'completed' => false,
        ]);
    }
}
