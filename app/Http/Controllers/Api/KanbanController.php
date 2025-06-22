<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Column;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class KanbanController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()->columns()->with('tasks')->orderBy('order')->get();
    }

    public function sync(Request $request)
    {
        $request->validate([
            'columns' => ['required', 'array'],
        ]);

        $user = $request->user();

        foreach ($request->columns as $columnIndex => $columnData) {
            $column = $user->columns()->findOrFail($columnData['id']);
            $column->update(['order' => $columnIndex]);

            foreach ($columnData['tasks'] as $taskIndex => $taskData) {
                $task = Task::findOrFail($taskData['id']);
                // Ensure the task belongs to a column owned by the user
                if ($task->column->user_id === $user->id) {
                    $task->update([
                        'order' => $taskIndex,
                        'column_id' => $columnData['id']
                    ]);
                }
            }
        }

        return response()->noContent();
    }

    public function storeTask(Request $request)
    {
        $user = $request->user();
        
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'column_id' => ['required', 'integer', Rule::exists('columns', 'id')->where('user_id', $user->id)],
        ]);
        
        $column = $user->columns()->findOrFail($request->column_id);

        $task = $column->tasks()->create([
            'title' => $request->title,
            'order' => $column->tasks()->count(),
        ]);

        return $task;
    }

    public function updateTask(Request $request, Task $task)
    {
        // Authorization: Check if the task's column belongs to the user
        $this->authorize('update', $task);

        $data = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $task->update($data);

        return $task;
    }

    public function destroyTask(Request $request, Task $task)
    {
        // Authorization: Check if the task's column belongs to the user
        $this->authorize('delete', $task);
        
        $task->delete();

        return response()->noContent();
    }
} 