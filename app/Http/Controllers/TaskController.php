<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class TaskController extends Controller
{
  private function getCacheKey(Request $request): string
  {
    $user_id = $request->user()->id;
    $params = $request->only(['search', 'category', 'completed', 'deadline', 'sort_by', 'sort_direction']);
    ksort($params);
    $paramsHash = md5(json_encode($params));

    $userKeysKey = "user.{$user_id}.cache_keys";
    $existingKeys = Cache::get($userKeysKey, []);
    $newKey = "tasks.{$user_id}.{$paramsHash}";

    if (!in_array($newKey, $existingKeys)) {
      Cache::put($userKeysKey, [...$existingKeys, $newKey], now()->addDay());
    }

    return $newKey;
  }

  public function index(Request $request): JsonResponse
  {
    try {
      $cacheKey = $this->getCacheKey($request);

      $tasks = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($request) {
        return $request->user()
          ->tasks()
          ->when($request->search, function ($query, $search) {
            return $query->where(function ($query) use ($search) {
              $query->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
            });
          })
          ->when($request->category, function ($query, $category) {
            return $query->where('category', $category);
          })
          ->when($request->has('completed'), function ($query) use ($request) {
            return $query->where('completed', filter_var($request->completed, FILTER_VALIDATE_BOOLEAN));
          })
          ->when($request->deadline, function ($query, $deadline) {
            return match ($deadline) {
              'overdue' => $query->where('deadline', '<', now())->where('completed', false),
              'today' => $query->whereDate('deadline', today()),
              'week' => $query->whereBetween('deadline', [now(), now()->endOfWeek()]),
              'month' => $query->whereBetween('deadline', [now(), now()->endOfMonth()]),
              default => $query
            };
          })
          ->when(
            $request->sort_by && in_array($request->sort_by, ['deadline', 'title', 'created_at']),
            function ($query) use ($request) {
              return $query->orderBy(
                $request->sort_by,
                $request->sort_direction === 'desc' ? 'desc' : 'asc'
              );
            },
            function ($query) {
              return $query->orderBy('deadline', 'asc');
            }
          )
          ->get();
      });

      return response()->json([
        'tasks' => $tasks,
        'debug' => [
          'cache_key' => $cacheKey,
          'from_cache' => Cache::has($cacheKey)
        ]
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to fetch tasks'
      ], 500);
    }
  }

  public function store(CreateTaskRequest $request): JsonResponse
  {
    try {
      $task = $request->user()->tasks()->create(
        $request->validated() + ['completed' => false]
      );

      $this->clearUserTaskCache($request);

      return response()->json([
        'status' => 'success',
        'message' => 'Task created successfully',
        'data' => ['task' => $task->fresh()]
      ], 201);
    } catch (\Exception $e) {
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to create task'
      ], 500);
    }
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

    $this->clearUserTaskCache($request);

    return response()->json([
      'status' => 'success',
      'message' => 'Task updated successfully',
      'data' => ['task' => $task->fresh()]
    ]);
  }

  public function destroy(Request $request, ?Task $task): JsonResponse
  {
    if (!$task) {
      return response()->json(['message' => 'Task not found'], 404);
    }

    if ($request->user()->cannot('delete', $task)) {
      return response()->json(['message' => 'Unauthorised'], 403);
    }

    $task->delete();

    $this->clearUserTaskCache($request);

    return response()->json([], 204);
  }

  private function clearUserTaskCache(Request $request): void
  {
    $user_id = $request->user()->id;
    $userKeysKey = "user.{$user_id}.cache_keys";

    $keys = Cache::get($userKeysKey, []);

    foreach ($keys as $key) {
      Cache::forget($key);
    }

    Cache::forget($userKeysKey);
  }
}
