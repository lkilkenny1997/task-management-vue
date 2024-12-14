<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TaskController extends Controller
{
  public function index(Request $request): JsonResponse
  {
    $tasks = $request->user()
      ->tasks()
      ->when($request->title, function ($query, $title) {
        return $query->where('title', 'like', '%' . $title . '%');
      })
      ->when($request->description, function ($query, $description) {
        return $query->where('description', 'like', '%' . $description . '%');
      })
      ->when($request->category, function ($query, $category) {
        return $query->where('category', $category);
      })
      ->when($request->has('completed'), function ($query) use ($request) {
        return $query->where('completed', filter_var($request->completed, FILTER_VALIDATE_BOOLEAN));
      })
      ->orderBy('deadline')
      ->get();

    return response()->json(['tasks' => $tasks]);
  }

  public function store(CreateTaskRequest $request): JsonResponse
  {
    $task = $request->user()->tasks()->create(
      $request->validated() + ['completed' => false]
    );

    return response()->json([
      'status' => 'success',
      'message' => 'Task created successfully',
      'data' => ['task' => $task->fresh()]
    ], 201);
  }

  public function show(Request $request, Task $task): JsonResponse
  {
    if ($request->user()->cannot('view', $task)) {
      return response()->json(['message' => 'Unauthorised'], 403);
    }

    return response()->json(['task' => $task]);
  }

  public function update(UpdateTaskRequest $request, Task $task): JsonResponse
  {
    if ($request->user()->cannot('update', $task)) {
      return response()->json([
        'status' => 'error',
        'message' => 'Unauthorised'
      ], 403);
    }

    $task->update($request->validated());

    return response()->json([
      'status' => 'success',
      'message' => 'Task updated successfully',
      'data' => ['task' => $task->fresh()]
    ]);
  }

  public function destroy(Request $request, Task $task): JsonResponse
  {
    if ($request->user()->cannot('delete', $task)) {
      return response()->json(['message' => 'Unauthorised'], 403);
    }

    $task->delete();

    return response()->json([], 204);
  }
}
