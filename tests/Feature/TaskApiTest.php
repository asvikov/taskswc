<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $anotherUser;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->anotherUser = User::factory()->create();
        
        $response = $this->postJson('/api/login', [
            'email' => $this->user->email,
            'password' => 'password'
        ]);
        
        $response->assertStatus(200);
        $this->token = $response->json('access_token');
    }

    public function test_can_list_tasks()
    {
        Task::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                         ->getJson('/api/tasks');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonCount(3, 'data');
    }

    public function test_can_filter_tasks_by_status()
    {
        Task::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'planned'
        ]);
        
        Task::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'in_progress'
        ]);
        
        Task::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'done'
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                         ->getJson('/api/tasks?status=planned');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', 'planned');
    }

    public function test_can_filter_tasks_by_user_id()
    {
        Task::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'planned'
        ]);
        
        Task::factory()->create([
            'user_id' => $this->anotherUser->id,
            'status' => 'in_progress'
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                         ->getJson('/api/tasks?user_id=' . $this->user->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.user_id', $this->user->id);
    }

    public function test_can_filter_tasks_by_end_date()
    {
        Task::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'planned',
            'end_date' => '2025-12-01'
        ]);
        
        Task::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'in_progress',
            'end_date' => '2025-12-02'
        ]);
        
        Task::factory()->create([
            'user_id' => $this->anotherUser->id,
            'status' => 'planned',
            'end_date' => '2025-12-01'
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                         ->getJson('/api/tasks?end_date_from=2025-12-02&end_date_to=2025-12-02');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.end_date', '2025-12-02T00:00:00.000000Z')
            ->assertJsonPath('data.0.user_id', $this->user->id);
    }

    public function test_can_filter_tasks_by_multiple_criteria()
    {
        Task::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'planned',
            'end_date' => '2025-12-01'
        ]);
        
        Task::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'planned',
            'end_date' => '2025-12-02'
        ]);
        
        Task::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'in_progress',
            'end_date' => '2025-12-01'
        ]);
        
        Task::factory()->create([
            'user_id' => $this->anotherUser->id,
            'status' => 'planned',
            'end_date' => '2025-12-01'
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                         ->getJson('/api/tasks?status=planned&end_date_from=2025-12-02&end_date_to=2025-12-02');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', 'planned')
            ->assertJsonPath('data.0.end_date', '2025-12-02T00:00:00.000000Z')
            ->assertJsonPath('data.0.user_id', $this->user->id);
    }

    public function test_can_create_task()
    {
        Mail::fake();
        
        $taskData = [
            'name' => 'Test Task',
            'description' => 'Test Description',
            'status' => 'planned'
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                         ->postJson('/api/tasks', $taskData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Task created successfully'
            ]);

        $this->assertDatabaseHas('tasks', [
            'name' => 'Test Task',
            'description' => 'Test Description',
            'user_id' => $this->user->id,
            'status' => 'planned'
        ]);
        
        Mail::assertQueued(\App\Mail\TaskCreated::class);
    }

    public function test_can_create_task_with_image()
    {
        Storage::fake('public');
        
        Mail::fake();

        $taskData = [
            'name' => 'Test Task with Image',
            'description' => 'Test Description with Image',
            'status' => 'planned',
            'image' => UploadedFile::fake()->image('test.jpg')
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                         ->postJson('/api/tasks', $taskData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Task created successfully'
            ]);

        $this->assertDatabaseHas('tasks', [
            'name' => 'Test Task with Image',
            'user_id' => $this->user->id,
            'status' => 'planned'
        ]);
        
        Mail::assertQueued(\App\Mail\TaskCreated::class);
    }

    public function test_email_is_sent_to_correct_user()
    {
        Mail::fake();
        
        $taskData = [
            'name' => 'Test Task',
            'description' => 'Test Description',
            'status' => 'planned'
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                         ->postJson('/api/tasks', $taskData);
                         
        Mail::assertQueued(\App\Mail\TaskCreated::class, function ($mail) {
            return $mail->hasTo($this->user->email);
        });
    }

    public function test_can_show_task()
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                         ->getJson("/api/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonFragment([
                'id' => $task->id,
                'name' => $task->name
            ]);
    }

    public function test_can_update_task()
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $updatedData = [
            'name' => 'Updated Task Name',
            'description' => 'Updated Description',
            'status' => 'in_progress'
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                         ->putJson("/api/tasks/{$task->id}", $updatedData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Task updated successfully'
            ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'name' => 'Updated Task Name',
            'description' => 'Updated Description',
            'status' => 'in_progress'
        ]);
    }

    public function test_can_delete_task()
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                         ->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Task deleted successfully'
            ]);

        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id
        ]);
    }

    public function test_validation_error_when_creating_task_without_required_fields()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                         ->postJson('/api/tasks', []);

        $response->assertStatus(422);
    }
}