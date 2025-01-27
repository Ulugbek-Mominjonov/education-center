<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeacherRequest;
use App\Http\Resources\TeacherResource;
use App\Models\Group;
use App\Models\Teacher;
use App\Models\User;
use App\Rules\ValidUser;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TeacherController extends Controller
{
    public function index()
    {
        $teachers = TeacherResource::collection(Teacher::all());
        return response()->json($teachers, 200);
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

        $query = Teacher::query();

        if ($request->has('first_name')) {
            $first_name = $request->query('first_name');
            $query->where('first_name', 'like', "%$first_name%");
        }

        if ($request->has('last_name')) {
            $last_name = $request->query('last_name');
            $query->where('last_name', 'like', "%$last_name%");
        }

        if ($request->has('phone')) {
            $phone = $request->query('phone');
            $query->where('phone', 'like', "%$phone%");
        }

        if ($request->has('degree')) {
            $degree = $request->query('degree');
            $query->where('degree', 'like', "%$degree%");
        }

        if ($request->has('salary')) {
            $salary = $request->query('salary');
            $query->where('salary', '=', $salary);
        }

        if ($request->has('subject_id')) {
            $subject_id = $request->query('subject_id');
            $query->whereHas('subjects', function ($query) use ($subject_id) {
                $query->where('subject_id', '=', $subject_id);
            });
        }

        if ($request->has('group_id')) {
            $group_id = $request->query('group_id');
            $query->whereHas('groups', function ($query) use ($group_id) {
                $query->where('group_id', '=', $group_id);
            });
        }

        $teachers = $query->with(['groups', 'subjects', 'user'])
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json(
            [
                'data' => TeacherResource::collection($teachers),
                'total' => $teachers->total(),
                'last_page' => $teachers->lastPage(),
                'current_page' => $teachers->currentPage(),
                'per_page' => $teachers->perPage()
            ],
            200
        );
    }

    public function store(StoreTeacherRequest $request)
    {
        try {
            $teacher = Teacher::create($request->all());
            $teacher->subjects()->attach($request->subjects);
            $teacher->groups()->attach($request->groups);
            return response()->json(new TeacherResource($teacher->load(['groups', 'subjects', 'user'])), 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {

        $teacher = Teacher::find($id);

        if (!$teacher) {
            return response()->json(['message' => 'teacher not found'], 404);
        }

        return response()->json(new TeacherResource($teacher->load(['groups', 'subjects', 'user'])), 200);
    }

    public function update(StoreTeacherRequest $request, $id)
    {
        $teacher = Teacher::find($id);

        if (!$teacher) {
            return response()->json(['message' => 'teacher not found'], 404);
        }

        try {
            $teacher->update($request->all());
            $teacher->subjects()->sync($request->subjects);
            $teacher->groups()->sync($request->groups);
            return response()->json(new TeacherResource($teacher->load(['groups', 'subjects', 'user'])), 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function attachUser(Request $request, $id)
    {
        $request->validate([
            'user_id' => [
                'required',
                'integer',
                new ValidUser,
            ],
        ]);
        $teacher = Teacher::find($id);

        if (!$teacher) {
            return response()->json(['message' => 'teacher not found'], 404);
        }

        if ($teacher->user_id) {
            return response()->json(['message' => 'teacher already attached'], 400);
        }

        try {
            $teacher->user_id = $request->user_id;
            $teacher->save();

            $teacher->user()->update(['is_attach' => true]);

            return response()->json(new TeacherResource($teacher->load(['user'])), 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function changeSubjects(Request $request, $id)
    {
        $request->validate([
            'subjects' => 'required|array',
            'subjects.*' => 'integer|exists:subjects,id',

        ]);
        $teacher = Teacher::find($id);

        if (!$teacher) {
            return response()->json(['message' => 'teacher not found'], 404);
        }

        try {
            $teacher->subjects()->sync($request->subjects);
            return response()->json(new TeacherResource($teacher->load(['subjects'])), 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function changeGroups(Request $request, $id)
    {
        $request->validate([
            'groups' => 'required|array',
            'groups.*' => 'integer|exists:groups,id',
        ]);
        $teacher = Teacher::find($id);

        if (!$teacher) {
            return response()->json(['message' => 'teacher not found'], 404);
        }

        $subjectIds = $teacher->subjects()->pluck('subject_id')->toArray();

        $errors = [];

        foreach ($request->groups as $groupId) {
            $group = Group::find($groupId);

            if (!$group || !in_array($group->subject_id, $subjectIds)) {
                $errors[] = "This teacher cannot teach this group: {$group->name}";
            }
        }

        if (!empty($errors)) {
            return response()->json([
                'message' => 'Validation error occurred.',
                'errors' => $errors,
            ], 400);
        }

        try {
            $teacher->groups()->sync($request->groups);
            return response()->json(new TeacherResource($teacher->load(['groups'])), 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $teacher = Teacher::find($id);

        if (!$teacher) {
            return response()->json(['message' => 'teacher not found'], 404);
        }

        try {
            $teacher->delete();
            return response()->json(['message' => 'teacher deleted'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
