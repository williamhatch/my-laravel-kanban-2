<?php

use App\Models\User;
use App\Models\Column;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user can fetch kanban data', function () {
    $user = User::factory()->create();
    $token = $user->createToken('auth_token')->plainTextToken;

    // Create columns and tasks
    $column = Column::factory()->create(['user_id' => $user->id]);
    Task::factory()->count(3)->create(['column_id' => $column->id]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->getJson('/api/kanban');

    $response->assertStatus(200)
        ->assertJsonStructure([
            '*' => [
                'id',
                'name',
                'tasks' => [
                    '*' => ['id', 'title', 'description', 'order']
                ]
            ]
        ]);
});

test('authenticated user can create a task', function () {
    $user = User::factory()->create();
    $token = $user->createToken('auth_token')->plainTextToken;
    $column = Column::factory()->create(['user_id' => $user->id]);

    $taskData = [
        'title' => 'New Task',
        'description' => 'Task description',
        'column_id' => $column->id,
    ];

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->postJson('/api/kanban/tasks', $taskData);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'id', 'title', 'description', 'column_id'
        ]);

    $this->assertDatabaseHas('tasks', [
        'title' => 'New Task',
        'column_id' => $column->id,
    ]);
});

test('unauthenticated user cannot access kanban data', function () {
    $response = $this->getJson('/api/kanban');
    $response->assertStatus(401);
}); 