<?php

namespace App\Http\Controllers;

use App\Http\Resources\GroupResource;
use App\Models\Group;
use App\Models\Subject;
use App\Models\Task;
use App\Models\Teacher;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function index()
    {
        $students = GroupResource::collection(Group::all());
        return response()->json($students, 200);
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

        $query = Group::query();

        if ($request->has('name')) {
            $name = $request->query('name');
            $query->where('name', 'like', "%$name%");
        }

        if ($request->has('label')) {
            $label = $request->query('label');
            $query->where('label', 'like', "%$label%");
        }

        if ($request->has('task_id')) {
            $task_id = $request->query('task_id');
            $query->whereHas('tasks', function ($query) use ($task_id) {
                $query->where('task_id', '=', $task_id);
            });
        }

        if ($request->has('teacher_id')) {
            $teacher_id = $request->query('teacher_id');
            $query->whereHas('teachers', function ($query) use ($teacher_id) {
                $query->where('teacher_id', '=', $teacher_id);
            });
        }

        $students = $query->with([
            'subject',
            'tasks',
            'teachers'
        ])
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json(
            [
                'data' => GroupResource::collection($students),
                'total' => $students->total(),
                'last_page' => $students->lastPage(),
                'current_page' => $students->currentPage(),
                'per_page' => $students->perPage()
            ],
            200
        );
    }

    public function show($id)
    {
        $student = Group::with([
            'subject',
            'tasks',
            'teachers'
        ])->find($id);

        if (!$student) {
            return response()->json(['message' => 'group not found'], 404);
        }
        return response()->json(new GroupResource($student), 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'label' => 'required|string',
            'price' => 'integer',
            'description' => 'string',
            'subject_id' => 'nullable|integer|exists:subjects,id',
        ]);
        try {
            $group = Group::create($request->only([
                'name',
                'label',
                'price',
                'description',
                'subject_id'
            ]));

            return response()->json(new GroupResource($group->load([
                'subject'
            ])), 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'string',
            'label' => 'string',
            'price' => 'integer',
            'description' => 'string',
            'subject_id' => 'nullable|integer|exists:subjects,id',
        ]);

        $group = Group::find($id);

        if (!$group) {
            return response()->json(['message' => 'group not found'], 404);
        }

        try {
            $group->update($request->only([
                'name',
                'label',
                'price',
                'description',
                'subject_id'
            ]));
            $group->groups()->sync($request->groups);
            return response()->json(new GroupResource($group->load([
                'subject'
            ])), 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function changeSubject(Request $request, $id)
    {
        $request->validate([
            'subject_id' => 'required|integer|exists:subjects,id',
        ]);

        $group = Group::find($id);

        if (!$group) {
            return response()->json(['message' => 'group not found'], 404);
        }

        try {
            $group->update($request->only([
                'subject_id'
            ]));
            return response()->json(new GroupResource($group->load([
                'subject'
            ])), 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function notAssingedTasks(Request $request, $id)
    {
        $group = Group::find($id);
        if (!$group) {
            return response()->json(['message' => 'group not found'], 404);
        }

        $tasks = Task::where('subject_id', $group->subject_id)->whereNotIn('id', $group->tasks()->pluck('task_id'))->get();
        return response()->json($tasks, 200);
    }

    public function attachTask(Request $request, $id)
    {
        $request->validate([
            'task_id' => 'required|integer|exists:tasks,id',
            'total_score' => 'required|integer',
            'deadline' => 'required|integer',
        ]);

        $group = Group::find($id);

        if (!$group) {
            return response()->json(['message' => 'group not found'], 404);
        }

        $task = Task::find($request->task_id);

        if ($group->subject_id != $task->subject_id) {
            return response()->json(['message' => 'task does not belong to this group'], 404);
        }

        if ($group->tasks()->where('task_id', $request->task_id)->exists()) {
            return response()->json(['message' => 'task already attached'], 400);
        }

        try {
            $group->tasks()->attach($request->task_id, [
                'total_score' => $request->total_score,
                'deadline' => $request->deadline
            ]);
            return response()->json(new GroupResource($group->load([
                'subject'
            ])), 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function notAttachedTeachers(Request $request, $id)
    {
        $group = Group::find($id);
        if (!$group) {
            return response()->json(['message' => 'group not found'], 404);
        }
        $teachers = Teacher::whereHas('subjects', function ($query) use ($group) {
            $query->where('subject_id', $group->subject_id);
        })->whereNotIn('id', $group->teachers()->pluck('teacher_id'))->get();

        return response()->json($teachers, 200);
    }

    public function attachTeachers(Request $request, $id)
    {
        $request->validate([
            'teachers_id' => 'required|array',
        ]);

        $group = Group::find($id);

        if (!$group) {
            return response()->json(['message' => 'group not found'], 404);
        };

        if ($group->teachers()->whereIn('teacher_id', $request->teachers_id)->exists()) {
            return response()->json(['message' => 'teachers already attached'], 400);
        };

        try {
            $group->teachers()->attach($request->teachers_id);
            return response()->json(new GroupResource($group->load([
                'subject'
            ])), 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $group = Group::find($id);
        if (!$group) {
            return response()->json(['message' => 'group not found'], 404);
        }

        try {
            $group->delete();
            return response()->json(['message' => 'group deleted'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function attachStudents(Request $request, $id)
    {
        $request->validate([
            'students_id' => [
                'required',
                'array'
            ],
        ]);

        $group = Group::find($id);

        if ($group->students()->count() >= 18) {
            return response()->json(['message' => 'group is full'], 400);
        }

        if (!$group) {
            return response()->json(['message' => 'group not found'], 404);
        };

        if ($group->students()->whereIn('student_id', $request->students_id)->exists()) {
            return response()->json(['message' => 'students already attached'], 400);
        };

        try {
            $group->students()->attach($request->students_id);
            return response()->json(new GroupResource($group->load([
                'students'
            ])), 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
