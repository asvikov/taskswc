<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskStoreRequest;
use App\Http\Requests\TaskUpdateRequest;
use App\Http\Requests\TaskIndexRequest;
use App\Mail\TaskCreated;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Exception;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(TaskIndexRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $query = Task::with('user')
            ->status($validated['status'] ?? null)
            ->userFilter($validated['user_id'] ?? null)
            ->endDateRange($validated['end_date_from'] ?? null, $validated['end_date_to'] ?? null);
            
            $tasks = $query->get();
            
            $tasks->each(function ($task) {
                if ($task->getFirstMedia('images')) {
                    $task->image_url = $task->getFirstMedia('images')->getUrl();
                }
                unset($task->media);
            });
            
            return $this->apiSuccessResponse(data: $tasks);
        } catch (Exception $e) {
            return $this->apiErrorResponse(message: 'Failed to retrieve tasks', error: $e);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TaskStoreRequest $request): JsonResponse
    {
        try {
            $task = auth()->user()->tasks()->create($request->validated());
            
            if ($request->hasFile('image')) {
                $task->addMediaFromRequest('image')
                     ->toMediaCollection('images');
                
                $task->image_url = $task->getFirstMedia('images')->getUrl();
            }
            
            $task->load('user');
            unset($task->media);
            
            Mail::to($task->user->email)->send(new TaskCreated($task));
            
            return $this->apiSuccessResponse(message: 'Task created successfully', data: $task, code: 201);
        } catch (Exception $e) {
            return $this->apiErrorResponse(message: 'Failed to create task', error: $e);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $task = Task::with('user')->findOrFail($id);
            
            if ($task->getFirstMedia('images')) {
                $task->image_url = $task->getFirstMedia('images')->getUrl();
            }

            unset($task->media);
            
            return $this->apiSuccessResponse(data: $task);
        } catch (Exception $e) {
            return $this->apiErrorResponse(message: 'Task not found', error: $e, code: 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TaskUpdateRequest $request, string $id): JsonResponse
    {
        try {
            $task = Task::findOrFail($id);
            
            $task->update($request->validated());
            
            if ($request->hasFile('image')) {
                $task->clearMediaCollection('images');
                
                $task->addMediaFromRequest('image')
                     ->toMediaCollection('images');
            }
            
            if ($task->getFirstMedia('images')) {
                $task->image_url = $task->getFirstMedia('images')->getUrl();
            }
            
            $task->load('user');
            unset($task->media);
            
            return $this->apiSuccessResponse(message: 'Task updated successfully', data: $task);
        } catch (Exception $e) {
            return $this->apiErrorResponse(message: 'Failed to update task', error: $e);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $task = Task::findOrFail($id);
            $task->delete();
            
            return $this->apiSuccessResponse(message: 'Task deleted successfully');
        } catch (Exception $e) {
            return $this->apiErrorResponse(message: 'Failed to delete task', error: $e);
        }
    }
}