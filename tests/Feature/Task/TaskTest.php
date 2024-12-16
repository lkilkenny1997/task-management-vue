<?php

namespace Tests\Feature\Task;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
  use RefreshDatabase;

  private User $user;

  protected function setUp(): void
  {
    parent::setUp();
    $this->user = User::factory()->create();
  }

  public function test_user_can_create_task(): void
  {
    $taskData = [
      'title' => 'Test Task',
      'description' => 'Test Description',
      'category' => 'work',
      'deadline' => now()->addDays(1)->toDateTimeString(),
    ];

    $response = $this->actingAs($this->user)
      ->postJson('/api/tasks', $taskData);

    $response->assertStatus(201)
      ->assertJsonStructure([
        'status',
        'message',
        'data' => [
          'task' => [
            'id',
            'user_id',
            'title',
            'description',
            'category',
            'deadline',
            'completed',
            'created_at',
            'updated_at'
          ]
        ]
      ])
      ->assertJson([
        'status' => 'success',
        'data' => [
          'task' => [
            'title' => $taskData['title'],
            'description' => $taskData['description'],
            'category' => $taskData['category'],
            'completed' => false
          ]
        ]
      ]);

    $this->assertDatabaseHas('tasks', [
      'user_id' => $this->user->id,
      'title' => 'Test Task',
    ]);
  }

  public function test_user_cannot_create_task_with_invalid_data(): void
  {
    $response = $this->actingAs($this->user)
      ->postJson('/api/tasks', []);

    $response->assertStatus(422)
      ->assertJsonValidationErrors(['title', 'category', 'deadline']);
  }

  public function test_user_cannot_create_task_with_invalid_category(): void
  {
    $response = $this->actingAs($this->user)
      ->postJson('/api/tasks', [
        'title' => 'Test Task',
        'category' => 'invalid',
        'deadline' => now()->addDays(1)->toDateTimeString(),
      ]);

    $response->assertStatus(422)
      ->assertJsonValidationErrors(['category']);
  }

  public function test_task_deadline_must_be_in_the_future(): void
  {
    $response = $this->actingAs($this->user)
      ->postJson('/api/tasks', [
        'title' => 'Test Task',
        'category' => 'work',
        'deadline' => now()->subDays(1)->toDateTimeString(),
      ]);

    $response->assertStatus(422)
      ->assertJsonValidationErrors(['deadline']);
  }

  public function test_user_can_view_their_tasks(): void
  {
    $tasks = Task::factory()->count(3)->create([
      'user_id' => $this->user->id
    ]);

    $response = $this->actingAs($this->user)
      ->getJson('/api/tasks');

    $response->assertOk()
      ->assertJsonStructure([
        'tasks' => []
      ])
      ->assertJsonCount(3, 'tasks');
  }

  public function test_user_can_update_their_task(): void
  {
    $task = Task::factory()->create([
      'user_id' => $this->user->id
    ]);

    $response = $this->actingAs($this->user)
      ->patchJson("/api/tasks/{$task->id}", [
        'title' => 'Updated Title',
        'category' => 'urgent'
      ]);

    $response->assertOk()
      ->assertJson([
        'status' => 'success',
        'message' => 'Task updated successfully'
      ]);

    $this->assertDatabaseHas('tasks', [
      'id' => $task->id,
      'title' => 'Updated Title',
      'category' => 'urgent'
    ]);
  }

  public function test_user_can_mark_task_as_completed(): void
  {
    $task = Task::factory()->create([
      'user_id' => $this->user->id,
      'completed' => false
    ]);

    $response = $this->actingAs($this->user)
      ->patchJson("/api/tasks/{$task->id}", [
        'completed' => true
      ]);

    $response->assertOk()
      ->assertJson([
        'status' => 'success',
        'message' => 'Task updated successfully'
      ]);

    $this->assertDatabaseHas('tasks', [
      'id' => $task->id,
      'completed' => true
    ]);
  }

  public function test_user_can_delete_their_task(): void
  {
    $task = Task::factory()->create([
      'user_id' => $this->user->id
    ]);

    $response = $this->actingAs($this->user)
      ->deleteJson("/api/tasks/{$task->id}");

    $response->assertStatus(204);
    $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
  }

  public function test_user_cannot_access_tasks_of_other_users(): void
  {
    $otherUser = User::factory()->create();
    $task = Task::factory()->create([
      'user_id' => $otherUser->id
    ]);

    $this->actingAs($this->user)
      ->getJson("/api/tasks/{$task->id}")
      ->assertForbidden()
      ->assertJson([
        'message' => 'Unauthorised'
      ]);

    $this->actingAs($this->user)
      ->patchJson("/api/tasks/{$task->id}", ['title' => 'Hacked!'])
      ->assertForbidden()
      ->assertJson([
        'message' => 'Unauthorised'
      ]);

    $this->actingAs($this->user)
      ->deleteJson("/api/tasks/{$task->id}")
      ->assertForbidden()
      ->assertJson([
        'message' => 'Unauthorised'
      ]);
  }

  public function test_user_can_filter_tasks_by_category(): void
  {
    Task::factory()->create([
      'user_id' => $this->user->id,
      'category' => 'work'
    ]);
    Task::factory()->create([
      'user_id' => $this->user->id,
      'category' => 'personal'
    ]);

    $response = $this->actingAs($this->user)
      ->getJson('/api/tasks?category=work');

    $response->assertOk()
      ->assertJson([
        'tasks' => [
          ['category' => 'work']
        ]
      ]);
  }

  public function test_user_can_filter_tasks_by_completed(): void
  {
    Task::factory()->create([
      'user_id' => $this->user->id,
      'completed' => true
    ]);
    Task::factory()->create([
      'user_id' => $this->user->id,
      'completed' => false
    ]);

    $response = $this->actingAs($this->user)
      ->getJson('/api/tasks?completed=true');

    $response->assertOk()
      ->assertJson([
        'tasks' => [
          ['completed' => true]
        ]
      ]);
  }
}
