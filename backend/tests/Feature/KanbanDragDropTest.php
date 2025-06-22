<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Column;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KanbanDragDropTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // 创建测试用户
        $this->user = User::factory()->create();
        
        // 创建测试列
        $this->backlogColumn = Column::factory()->create([
            'name' => 'Backlog',
            'user_id' => $this->user->id,
        ]);
        
        $this->upNextColumn = Column::factory()->create([
            'name' => 'Up Next', 
            'user_id' => $this->user->id,
        ]);
        
        $this->inProgressColumn = Column::factory()->create([
            'name' => 'In Progress',
            'user_id' => $this->user->id,
        ]);
        
        $this->doneColumn = Column::factory()->create([
            'name' => 'Done',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_can_sync_task_moved_between_columns()
    {
        // 创建测试任务
        $task1 = Task::factory()->create([
            'title' => 'Task 1',
            'column_id' => $this->backlogColumn->id,
            'order' => 0,
        ]);
        
        $task2 = Task::factory()->create([
            'title' => 'Task 2',
            'column_id' => $this->backlogColumn->id,
            'order' => 1,
        ]);
        
        $task3 = Task::factory()->create([
            'title' => 'Task 3',
            'column_id' => $this->upNextColumn->id,
            'order' => 0,
        ]);

        // 模拟拖拽：将task1从Backlog移动到Up Next
        $syncData = [
            'columns' => [
                [
                    'id' => $this->backlogColumn->id,
                    'name' => 'Backlog',
                    'tasks' => [
                        [
                            'id' => $task2->id,
                            'title' => 'Task 2',
                            'column_id' => $this->backlogColumn->id,
                            'order' => 0,
                        ]
                    ]
                ],
                [
                    'id' => $this->upNextColumn->id,
                    'name' => 'Up Next',
                    'tasks' => [
                        [
                            'id' => $task1->id,
                            'title' => 'Task 1',
                            'column_id' => $this->upNextColumn->id,
                            'order' => 0,
                        ],
                        [
                            'id' => $task3->id,
                            'title' => 'Task 3',
                            'column_id' => $this->upNextColumn->id,
                            'order' => 1,
                        ]
                    ]
                ],
                [
                    'id' => $this->inProgressColumn->id,
                    'name' => 'In Progress',
                    'tasks' => []
                ],
                [
                    'id' => $this->doneColumn->id,
                    'name' => 'Done',
                    'tasks' => []
                ]
            ]
        ];

        $response = $this->actingAs($this->user)
            ->putJson('/api/kanban/sync', $syncData);

        $response->assertStatus(200);

        // 验证数据库中的更改
        $this->assertDatabaseHas('tasks', [
            'id' => $task1->id,
            'column_id' => $this->upNextColumn->id,
            'order' => 0,
        ]);
        
        $this->assertDatabaseHas('tasks', [
            'id' => $task2->id,
            'column_id' => $this->backlogColumn->id,
            'order' => 0,
        ]);
        
        $this->assertDatabaseHas('tasks', [
            'id' => $task3->id,
            'column_id' => $this->upNextColumn->id,
            'order' => 1,
        ]);
    }

    public function test_can_sync_task_reordered_within_same_column()
    {
        // 创建测试任务
        $task1 = Task::factory()->create([
            'title' => 'Task 1',
            'column_id' => $this->backlogColumn->id,
            'order' => 0,
        ]);
        
        $task2 = Task::factory()->create([
            'title' => 'Task 2',
            'column_id' => $this->backlogColumn->id,
            'order' => 1,
        ]);
        
        $task3 = Task::factory()->create([
            'title' => 'Task 3',
            'column_id' => $this->backlogColumn->id,
            'order' => 2,
        ]);

        // 模拟拖拽：在Backlog列内重新排序（task3移到第一位）
        $syncData = [
            'columns' => [
                [
                    'id' => $this->backlogColumn->id,
                    'name' => 'Backlog',
                    'tasks' => [
                        [
                            'id' => $task3->id,
                            'title' => 'Task 3',
                            'column_id' => $this->backlogColumn->id,
                            'order' => 0,
                        ],
                        [
                            'id' => $task1->id,
                            'title' => 'Task 1',
                            'column_id' => $this->backlogColumn->id,
                            'order' => 1,
                        ],
                        [
                            'id' => $task2->id,
                            'title' => 'Task 2',
                            'column_id' => $this->backlogColumn->id,
                            'order' => 2,
                        ]
                    ]
                ],
                [
                    'id' => $this->upNextColumn->id,
                    'name' => 'Up Next',
                    'tasks' => []
                ],
                [
                    'id' => $this->inProgressColumn->id,
                    'name' => 'In Progress',
                    'tasks' => []
                ],
                [
                    'id' => $this->doneColumn->id,
                    'name' => 'Done',
                    'tasks' => []
                ]
            ]
        ];

        $response = $this->actingAs($this->user)
            ->putJson('/api/kanban/sync', $syncData);

        $response->assertStatus(200);

        // 验证数据库中的更改
        $this->assertDatabaseHas('tasks', [
            'id' => $task3->id,
            'column_id' => $this->backlogColumn->id,
            'order' => 0,
        ]);
        
        $this->assertDatabaseHas('tasks', [
            'id' => $task1->id,
            'column_id' => $this->backlogColumn->id,
            'order' => 1,
        ]);
        
        $this->assertDatabaseHas('tasks', [
            'id' => $task2->id,
            'column_id' => $this->backlogColumn->id,
            'order' => 2,
        ]);
    }

    public function test_unauthenticated_user_cannot_sync_kanban()
    {
        $syncData = [
            'columns' => []
        ];

        $response = $this->putJson('/api/kanban/sync', $syncData);

        $response->assertStatus(401);
    }

    public function test_sync_validates_required_data()
    {
        $response = $this->actingAs($this->user)
            ->putJson('/api/kanban/sync', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['columns']);
    }

    public function test_can_handle_empty_columns()
    {
        $syncData = [
            'columns' => [
                [
                    'id' => $this->backlogColumn->id,
                    'name' => 'Backlog',
                    'tasks' => []
                ],
                [
                    'id' => $this->upNextColumn->id,
                    'name' => 'Up Next',
                    'tasks' => []
                ],
                [
                    'id' => $this->inProgressColumn->id,
                    'name' => 'In Progress',
                    'tasks' => []
                ],
                [
                    'id' => $this->doneColumn->id,
                    'name' => 'Done',
                    'tasks' => []
                ]
            ]
        ];

        $response = $this->actingAs($this->user)
            ->putJson('/api/kanban/sync', $syncData);

        $response->assertStatus(200);
    }
} 