<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index()
    {
        return response()->json(Task::all(), 200);
    }

    public function paginate(Request $request)
    {
        $request->validate([
            'per_page' => 'integer',
            'page' => 'integer',
            'sort_by' => 'string',
            'sort_direction' => 'string|in:asc,desc',
        ]);

        $perPage = $request->query('per_page', 10);
        $page = $request->query('page', 1);
        $sortBy = $request->query('sort_by', 'id');
        $sortDirection = $request->query('sort_direction', 'desc');

        $query = Task::query();

        if ($request->has('subject_id')) {
            $subject_id = $request->query('subject_id');
            $query->where('subject_id', $subject_id);
        }


        $tasks = $query
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json(
            $tasks,
            200
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'subject_id' => 'required|integer|exists:subjects,id',
        ]);

        try {
            $task = Task::create($request->only(['subject_id', 'name', 'description']));
            return response()->json($task, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {

        $task = Task::find($id);

        if (!$task) {
            return response()->json(['message' => 'task not found'], 404);
        }

        return response()->json($task, 200);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'subject_id' => 'required|integer|exists:subjects,id',
        ]);
        $task = Task::find($id);

        if (!$task) {
            return response()->json(['message' => 'task not found'], 404);
        }

        try {
            $task->update($request->only(['subject_id', 'name', 'description']));
            return response()->json($task, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json(['message' => 'task not found'], 404);
        }

        try {
            $task->delete();
            return response()->json(['message' => 'task deleted'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
