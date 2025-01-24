<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeacherRequest;
use App\Http\Resources\TeacherResource;
use App\Models\Teacher;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function index()
    {
        return response()->json(TeacherResource::collection(Teacher::all()->load(['groups', 'subjects', 'user'])), 200);
    }

    public function paginate(Request $request)
    {
        $request->validate([
            'per_page' => 'integer',
            'page' => 'integer',
            'orderBy' => 'string',
            'orderDirection' => 'string|in:asc,desc',
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
            return response()->json(['message' => 'teacher not updated'], 500);
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
            return response()->json(['message' => 'teacher not deleted'], 500);
        }
    }
}
