<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Column;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class KanbanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $columns = Column::with('tasks')->get();
        return response()->json($columns);
    }

    /**
     * Store a newly created task in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'column_id' => 'required|exists:columns,id',
        ]);

        $task = Task::create([
            'title' => $validated['title'],
            'column_id' => $validated['column_id'],
            'description' => $request->description ?? '',
            // Assign a high order to place it at the end of the list
            'order' => Task::where('column_id', $validated['column_id'])->count(),
        ]);

        return response()->json($task, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'column_id' => 'sometimes|required|exists:columns,id',
            'order' => 'sometimes|required|integer|min:0',
        ]);

        \Log::info('Updating single task', [
            'task_id' => $task->id,
            'old_column_id' => $task->column_id,
            'old_order' => $task->order,
            'new_data' => $validated
        ]);

        $task->update($validated);

        \Log::info('Task updated successfully', [
            'task_id' => $task->id,
            'new_column_id' => $task->column_id,
            'new_order' => $task->order
        ]);

        return response()->json($task);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task): JsonResponse
    {
        $task->delete();

        return response()->json(null, 204);
    }

    /**
     * Sync the state of the board.
     */
    public function sync(Request $request): JsonResponse
    {
        $request->validate([
            'columns' => ['required', 'array']
        ]);

        \Log::info('Sync request received', ['columns' => $request->columns]);

        foreach ($request->columns as $column) {
            \Log::info('Processing column', ['column_id' => $column['id'], 'tasks_count' => count($column['tasks'])]);

            foreach ($column['tasks'] as $i => $task) {
                $taskModel = Task::find($task['id']);
                if ($taskModel) {
                    \Log::info('Updating task', [
                        'task_id' => $task['id'],
                        'old_column_id' => $taskModel->column_id,
                        'new_column_id' => $column['id'],
                        'old_order' => $taskModel->order,
                        'new_order' => $i
                    ]);

                    $taskModel->order = $i;
                    $taskModel->column_id = $column['id'];
                    $taskModel->save();
                }
            }
        }

        return response()->json(['message' => 'Sync completed'], 200);
    }
}
